<?php
// run user gatekeeper
admin_gatekeeper();

// get uploaded file
$xml = get_uploaded_file('upload');

// get import type
$type = strtolower(get_input('type'));

$importer = new PluginSettingsImporter();

// process the XML
$importer->execute($xml, $type);

// forward user to the admin tools menu
forward('admin/plugins');
