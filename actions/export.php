<?php
// run user gatekeeper
admin_gatekeeper();

$exporter = new PluginSettingsExporter();
$exporter->execute();
$exporter->output();
exit;