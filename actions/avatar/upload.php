<?php

/**
 * Avatar crop action
 */
$guid = get_input('guid');
$owner = get_entity($guid);

if (!$owner instanceof ElggUser || !$owner->canEdit()) {
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
		$filehandler->open('write');
		$filehandler->close();

		$imginfo = getimagesize($_FILES['avatar']['tmp_name']);

		$requiredMemory1 = ceil($imginfo[0] * $imginfo[1] * 5.35);
		$requiredMemory2 = ceil($imginfo[0] * $imginfo[1] * ($imginfo['bits'] / 8) * $imginfo['channels'] * 2.5);
		$requiredMemory = (int)max($requiredMemory1, $requiredMemory2);

		$mem_avail = elgg_get_ini_setting_in_bytes('memory_limit');
		$mem_used = memory_get_usage();
		
		$mem_avail = $mem_avail - $mem_used - 2097152; // 2 MB buffer
		
		if ($requiredMemory > $mem_avail) {
			// we don't have enough memory for any manipulation
			register_error(elgg_echo('arck:avatar:image_too_large'));
			forward(REFERER);
		}

		move_uploaded_file($_FILES['avatar']['tmp_name'], $filehandler->getFilenameOnFilestore());

		if (is_array($imginfo) && $imginfo[0] && $imginfo[1]) {
			$dimension = min($imginfo[0], $imginfo[1]);
			$x1 = ($imginfo[0] - $dimension) / 2;
			$y1 = ($imginfo[1] - $dimension) / 2;
			$x2 = $x1 + $dimension;
			$y2 = $y1 + $dimension;
		}
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
