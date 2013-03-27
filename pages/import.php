<?php
// run user gatekeeper
admin_gatekeeper ();

elgg_set_context('admin');

$title = elgg_echo('pluginsettings:title:import');	

// check if XMLReader is defined
if (!class_exists('XMLReader')) {
	register_error(elgg_echo('pluginsettings:error:xmlreader'));
}

$body = elgg_view_layout('admin', array(
	'content' => elgg_view('pluginsettings/import'), 
	'title' => $title
));

// Draw it
echo elgg_view_page($title, $body, 'admin');
