<?php

/**
 * Avatar upload form
 * 
 * @uses $vars['entity']
 */
$entity = elgg_extract('entity', $vars);

// Upload module
$upload_title = elgg_echo('avatar:upload');
$upload_mod = '<div class="elgg-text-help">' . elgg_echo('avatar:upload:instructions') . '</div>';
$upload_mod .= elgg_view("input/file", array(
	'name' => 'avatar',
	'class' => 'avatar-upload-input'
		));

echo elgg_view_module('aside', $upload_title, $upload_mod, array(
	'class' => 'avatar-upload-module',
));

// Cropper module
$cropper_title = elgg_echo('avatar:crop:title');
$cropper_mod = elgg_format_element('p', array('class' => 'elgg-text-help'), elgg_echo('avatar:create:instructions'));
if ($entity->icontime) {
	$x = $y = 0;
	$width = $height = 200;
	if ($entity->x2 > $entity->x1 && $entity->y2 > $entity->y1) {
		$x = (int) $entity->x1;
		$y = (int) $entity->y1;
		$width = (int) $entity->x2 - (int) $entity->x1;
		$height = (int) $entity->y2 - (int) $entity->y1;
	}

	$img = elgg_view('output/img', array(
		'src' => $entity->getIconURL('master'),
		'alt' => elgg_echo('avatar'),
		'data-x' => $x,
		'data-y' => $y,
		'data-width' => $width,
		'data-height' => $height,
	));
	$cropper_mod .= elgg_format_element('div', ['class' => 'avatar-cropper-preview'], $img);
}

foreach (array('x1', 'y1', 'x2', 'y2') as $coord) {
	$cropper_mod .= elgg_view('input/hidden', array(
		'name' => $coord,
		'value' => (int) $entity->coord,
		"data-$coord" => true,
	));
}

echo elgg_view_module('aside', $cropper_title, $cropper_mod, array(
	'class' => 'avatar-cropper-module hidden',
));

$footer = elgg_view('input/submit', array(
	'value' => elgg_echo('save'),
	'disabled' => true,
	'class' => 'elgg-state-disabled elgg-button-submit',
		));

$footer .= elgg_view('input/hidden', array(
	'name' => 'guid',
	'value' => $entity->guid
		));

echo elgg_format_element('div', ['class' => 'elgg-foot'], $footer);
