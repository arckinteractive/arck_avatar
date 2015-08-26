<?php

elgg_register_event_handler('init', 'system', 'arck_avatar_init');

/**
 * Initialize the plugin
 * @return void
 */
function arck_avatar_init() {

	elgg_register_action('avatar/upload', __DIR__ . '/actions/avatar/upload.php');

	elgg_register_plugin_hook_handler('route', 'avatar', 'arck_avatar_router');

	elgg_define_js('cropper', array(
		'src' => '/mod/arck_avatar/vendors/jquery.cropper/cropper.min.js',
		'deps' => array('jquery'),
	));

	elgg_register_css('jquery.cropper', '/mod/arck_avatar/vendors/jquery.cropper/cropper.min.css');
	elgg_register_css('arck.avatar', elgg_get_simplecache_url('css', 'arck/avatar.css'));
}

/**
 * Replace avatar edit page with a custom view
 * /avatar/edit/<guid>
 * 
 * @param string $hook   "route"
 * @param string $type   "avatar"
 * @param array  $return Identifier and segments
 * @return array
 */
function arck_avatar_router($hook, $type, $return) {

	$identifier = elgg_extract('handler', $return);
	$segments = elgg_extract('segments', $return);

	if ($identifier == 'avatar' && $segments[0] == 'edit') {
		echo elgg_view('resources/avatar/edit', array(
			'entity' => get_user_by_username($segments[1]),
		));
		return false;
	}

	return $return;
}