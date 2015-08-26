<?php

/**
 * Avatar crop action
 */
$guid = get_input('guid');
$owner = get_entity($guid);

if (!$owner || !($owner instanceof ElggUser) || !$owner->canEdit()) {
	register_error(elgg_echo('avatar:upload:fail'));
	forward(REFERER);
}

$x1 = (int) get_input('x1', 0);
$y1 = (int) get_input('y1', 0);
$x2 = (int) get_input('x2', 0);
$y2 = (int) get_input('y2', 0);

$filehandler = new ElggFile();
$filehandler->owner_guid = $owner->guid;
$filehandler->setFilename("profile/{$owner->guid}master.jpg");

if (!empty($_FILES['avatar']['tmp_name'])) {
	if ($_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
		move_uploaded_file($_FILES['avatar']['tmp_name'], $filehandler->getFilenameOnFilestore());
	}
}

if (!$filehandler->exists()) {
	register_error(elgg_echo('avatar:upload:fail'));
	forward(REFERER);
}

$icon_sizes = elgg_get_config('icon_sizes');
unset($icon_sizes['master']);

$files = array($filehandler);

foreach ($icon_sizes as $name => $size_info) {
	$resized = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(), $size_info['w'], $size_info['h'], $size_info['square'], $x1, $y1, $x2, $y2, $size_info['upscale']);
	if (!$resized) {
		unset($owner->icontime);
		unset($owner->x1);
		unset($owner->x2);
		unset($owner->y1);
		unset($owner->y2);

		foreach ($files as $file) {
			$file->delete();
		}
		register_error(elgg_echo('avatar:resize:fail'));
		forward(REFERER);
	}

	$file = new ElggFile();
	$file->owner_guid = $owner->guid;
	$file->setFilename("profile/{$owner->guid}{$name}.jpg");
	$file->open('write');
	$file->write($resized);
	$file->close();
	$files[] = $file;
}

$owner->icontime = time();

$owner->x1 = $x1;
$owner->x2 = $x2;
$owner->y1 = $y1;
$owner->y2 = $y2;

system_message(elgg_echo('avatar:upload:success'));
$view = 'river/user/default/profileiconupdate';
elgg_delete_river(array('subject_guid' => $owner->guid, 'view' => $view));
add_to_river($view, 'update', $owner->guid, $owner->guid);

forward(REFERER);