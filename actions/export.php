<?php
// run user gatekeeper
admin_gatekeeper();

$exporter = new PluginSettingsExporter();
echo $exporter->execute();
exit;