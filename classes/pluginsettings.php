<?php
class pluginsettings {
	
	static function init() {
		elgg_register_action("pluginsettings/import", elgg_get_config('pluginspath') . "pluginsettings/actions/import.php", 'admiin');
		elgg_register_action("pluginsettings/export", elgg_get_config('pluginspath') . "pluginsettings/actions/export.php", 'admin');
		elgg_register_page_handler('pluginsettings', array(__CLASS__, 'page_handler'));
		elgg_register_event_handler('pagesetup', 'system', array(__CLASS__, 'pagesetup'));
	}
	
	static function pagesetup() {
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
	
	static function page_handler($page) {
		if (isset($page[0])) {
			switch ($page[0]) {
				case "export" :
					include elgg_get_config('pluginspath') . __CLASS__ . "/pages/export.php";
					return true;
					break;
				case "import" :
					include elgg_get_config('pluginspath') . __CLASS__ . "/pages/import.php";
					if ($page[1]) {
						set_input('config', $page[1]);
					}
					return true;
					break;
			}
		}
		include elgg_get_config('pluginspath') . __CLASS__ . "/pages/export.php";
		return true;
	}
}