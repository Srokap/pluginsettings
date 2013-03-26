<div id="content_area_user_title">
	<h2><?php echo elgg_echo( 'pluginsettings:title:export' ); ?></h2>
</div>
<form id="changeusernameform" action="<?php echo $CONFIG->wwwroot; ?>action/pluginsettings/export" method="post">

<?php 
	echo elgg_echo( 'pluginsettings:label:export' ); 
	echo elgg_view('input/securitytoken');
	
	echo elgg_view('input/submit',array('value' => 'Export'));
?>
</form>