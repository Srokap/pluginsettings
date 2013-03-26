<?php
// run user gatekeeper
admin_gatekeeper ();

set_context('admin');

// create view
$title = elgg_echo('pluginsettings:title:import');	
$content = elgg_view('pluginsettings/import');
    
// Format page
$body = elgg_view_layout('two_column_left_sidebar', null, $content);

// check if XMLReader is defined
if (!class_exists('XMLReader')) {
	register_error(elgg_echo('pluginsettings:error:xmlreader'));
}

// Draw it
echo elgg_view_page($title, $body, 'admin');
