<?php
function pluginsettings_init() {
	elgg_register_action("pluginsettings/import", elgg_get_config('pluginspath') . "pluginsettings/actions/import.php", 'admiin');
	elgg_register_action("pluginsettings/export", elgg_get_config('pluginspath') . "pluginsettings/actions/export.php", 'admin');
	elgg_register_page_handler('pluginsettings', 'pluginsettings_page_handler');
	elgg_register_event_handler('pagesetup', 'system', 'pluginsettings_pagesetup');
}

function pluginsettings_pagesetup() {

	if (elgg_get_context() == 'admin' && elgg_is_admin_logged_in()) {
		elgg_register_menu_item('page', ElggMenuItem::factory(array(
			'name' => 'pluginsettings/export',
			'href' => 'pluginsettings/export',
			'text' => elgg_echo('pluginsettings:menu:export'),
			'section' => 'configure',
			'parent_name' => 'settings',
			'priority' => 40,
		)));
		elgg_register_menu_item('page', ElggMenuItem::factory(array(
			'name' => 'pluginsettings/import',
			'href' => 'pluginsettings/import',
			'text' => elgg_echo('pluginsettings:menu:import'),
			'section' => 'configure',
			'parent_name' => 'settings',
			'priority' => 40,
		)));
	}
}	

function pluginsettings_page_handler($page) {

	if (isset ( $page [0] )) {
		switch ($page [0]) {
			case "export" :
				include (dirname ( __FILE__ ) . "/pages/export.php");
				return true;
				break;
			case "import" :
				include (dirname ( __FILE__ ) . "/pages/import.php");
				if ($page[1]) {
					set_input('config', $page[1]);
				}
				return true;
				break;
		}
	}
	include (dirname ( __FILE__ ) . "/pages/export.php");
	return true;
}

elgg_register_event_handler('init','system','pluginsettings_init');
