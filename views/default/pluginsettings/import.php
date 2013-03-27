<?php 
echo elgg_view_module('main', elgg_echo('pluginsettings:warning'), elgg_echo( 'pluginsettings:import:warning')); 
?>
<form id="changeusernameform" action="<?php echo $CONFIG->wwwroot; ?>action/pluginsettings/import" enctype="multipart/form-data" method="post">
<?php 	
	$radio_gaga = array();
	$radio_gaga['options'] = array(elgg_echo( 'pluginsettings:label:fullimport' ) => 'full', elgg_echo( 'pluginsettings:label:core' ) => 'core', elgg_echo( 'pluginsettings:label:plugins' ) => 'plugins');
	$radio_gaga['internalname'] = 'type';
	$radio_gaga['value'] = 'plugins';
	echo elgg_view('input/securitytoken');
	echo elgg_view("input/radio", $radio_gaga);
	echo elgg_echo( 'pluginsettings:label:import' ); 
	echo elgg_view("input/file",array('internalname' => 'upload'));
	
	echo elgg_view('input/submit',array('value' => 'Import'));
?>
</form>