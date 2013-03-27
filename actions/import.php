<?php
// run user gatekeeper
admin_gatekeeper ();

// get uploaded file
$xml = get_uploaded_file ( 'upload' );

// get import type
$type = strtolower(get_input('type'));

$importer = new PluginSettingsImporter();

// process the XML array
$importer->execute($xml, $type);

// clear out all site caches
elgg_regenerate_simplecache();
elgg_reset_system_cache();

// forward user to the admin tools menu
forward('admin/plugins');
