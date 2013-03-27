<form id="changeusernameform" action="<?php echo $CONFIG->wwwroot; ?>action/pluginsettings/export" method="post">

<?php 
	echo elgg_echo( 'pluginsettings:label:export' ); 
	echo elgg_view('input/securitytoken');
	
	echo elgg_view('input/submit',array('value' => 'Export'));
?>
</form>