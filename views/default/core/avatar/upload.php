<?php
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggEntity) {
	return;
}

$user_avatar = elgg_view('output/img', array(
	'src' => $entity->getIconUrl('medium'),
	'alt' => elgg_echo('avatar'),
		));

$remove_button = '';
if ($entity->icontime) {
	$remove_button = elgg_view('output/url', array(
		'text' => elgg_echo('remove'),
		'title' => elgg_echo('avatar:remove'),
		'href' => 'action/avatar/remove?guid=' . $entity->guid,
		'is_action' => true,
		'class' => 'elgg-button elgg-button-cancel mtm',
	));
}

$form_params = array('enctype' => 'multipart/form-data');
$upload_form = elgg_view_form('avatar/upload', $form_params, $vars);

$current = elgg_view_module('aside', elgg_echo('avatar:current'), $user_avatar, array(
	'footer' => $remove_button,
		));

echo elgg_view_image_block($current, $upload_form, array(
	'class' => 'avatar-upload-image-block',
));