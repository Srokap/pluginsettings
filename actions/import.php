<?php
// run user gatekeeper
admin_gatekeeper ();

// get uploaded file
$file = get_uploaded_file ( 'upload' );

// get import type
$type = strtolower(get_input('type'));

// read the xml file that was uploaded
$xml = new XMLReader ( );
$xml->setParserProperty ( XMLReader::VALIDATE, true );
$xml->XML ( $file );

// parse the XML file into an array
$xml_array = PluginSettingsImporter::xml2assoc($xml);

// process the XML array
PluginSettingsImporter::getInstance()->processImportXML($xml_array, $type);

// clear out all site caches
elgg_regenerate_simplecache();
elgg_reset_system_cache();

// forward user to the admin tools menu
forward('admin/plugins');
