<?php

elgg_push_context('settings');
elgg_push_context('profile_edit');

$title = elgg_echo('avatar:edit');
$content = elgg_view('arck/avatar', $vars);

if (!$content) {
	register_error(elgg_echo('avatar:noaccess'));
	forward('profile');
}

$params = array(
	'content' => $content,
	'title' => $title,
	'filter' => false,
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
