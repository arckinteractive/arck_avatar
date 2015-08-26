<?php

$entity = elgg_extract('entity', $vars);
if (!elgg_instanceof($entity, 'user') || !$entity->canEdit()) {
	return;
}

elgg_load_css('jquery.cropper');
elgg_load_css('arck.avatar');
elgg_require_js('arck/avatar');

echo elgg_view('core/avatar/upload', array('entity' => $entity));

