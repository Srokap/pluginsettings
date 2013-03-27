<?php
// run user gatekeeper
admin_gatekeeper ();

elgg_set_context('admin');

$title = elgg_echo('pluginsettings:title:export');	

// check if XMLWriter is defined
if (!class_exists('XMLWriter')) {
	register_error(elgg_echo('pluginsettings:error:xmlwriter'));
}

$body = elgg_view_layout('admin', array(
	'content' => elgg_view('pluginsettings/export'),
	'title' => $title
));

// Draw it
echo elgg_view_page($title, $body, 'admin');
