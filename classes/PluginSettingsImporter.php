<?php
/**
 * Contains functions used for importing
 */
class PluginSettingsImporter {
	
	/**
	 * Processes the XML array and farms off work to functions outside the main node
	 * @param array $xml
	 * @return null
	 */
	public function execute($xml, $type) {
		
		// read the xml string
		$reader = new XMLReader ( );
		$reader->setParserProperty ( XMLReader::VALIDATE, true );
		$reader->XML ( $xml );
		// parse the XML file into an array
		$xml_array = $this->xml2assoc($reader);
		
		$core = array();
		$plugins = array();

		foreach ( $xml_array as $outernode ) {
	
			if (is_array ( $outernode )) {
				if ($outernode ['tag'] == 'elgg') {
					foreach ( $outernode as $components ) {
							
						if (is_array ( $components )) {
							foreach ( $components as $component ) {
									
								if (is_array ( $component )) {
									if ($component ['tag'] == 'core') {
										$core = $component ['value'];
									}
	
									if ($component ['tag'] == 'plugins') {
										$plugins = $component ['value'];
									}
								}
							}
						}
					}
				}
			}
		}
		if (($type == 'core') || ($type == 'full')) {
			$this->processCore ( $core );
		}
		if (($type == 'plugins') || ($type == 'full')) {
			$this->processPlugins ( $plugins );
		}
	}
	
	/**
	 * Processes, resers and restores core settings
	 * @return null
	 */
	private function processCore($core) {
		//var_dump($core);
		if ($core) {
			$config = array();
			$database = array();
			$site = array();
	
			foreach ($core as $node) {
				if ($node['tag'] == 'database') {
					$database = $node['value'];
				}
				if ($node['tag'] == 'config') {
					$config = $node['value'];
				}
				if ($node['tag'] == 'site') {
					$site = $node['value'];
				}
			}
	
			$this->processDatabase($database);
			$guid = $this->processSite($site);
			$this->processConfig($config, $guid);
		}
	}
	
	/**
	 * @param unknown_type $database
	 * @return null
	 */
	private function processDatabase($database) {
	
		$db_vars = $this->array2hash($database[0]["value"]);
	
		$save_vars = array();
		$save_vars['CONFIG_DBUSER']   = $db_vars['username'];
		$save_vars['CONFIG_DBPASS']   = $db_vars['password'];
		$save_vars['CONFIG_DBNAME']   = $db_vars['database'];
		$save_vars['CONFIG_DBHOST']   = $db_vars['host'];
		$save_vars['CONFIG_DBPREFIX'] = 'elgg';
	
		$result = create_settings($save_vars,  dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) )  . "/engine/settings.example.php");
		file_put_contents( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) . "/engine/settings.php", $result);
	}
	
	/**
	 * @param unknown_type $config
	 * @param unknown_type $guid
	 * @return null
	 */
	private function processConfig($config, $guid) {
		$config = $this->array2hash($config);
		//var_dump($config);
	
		if (!datalist_get('installed')) {
			datalist_set('installed', time());
		}
	
		datalist_set('path', $config['path']);
		datalist_set('dataroot', $config['dataroot']);
	
		datalist_set('default_site',$guid);
	
		set_config('view', $config['view'], $guid);
		set_config('language', $config['language'], $guid);
		set_config('default_access', $config['default_access'], $guid);
	
		if ($config['debug'])
			set_config('debug', 1, $guid);
		else
			unset_config('debug', $guid);
	
		if ($config['ping_home'])
			unset_config('ping_home', $guid);
		else
			set_config('ping_home', 'disabled', $guid);
	
		if ($config['enable_api'])
			unset_config('disable_api', $guid);
		else
			set_config('disable_api', 'disabled', $guid);
	
		if ($config['https_login'])
			set_config('https_login', 1, $guid);
		else
			unset_config('https_login', $guid);
	
	}
	
	/**
	 * @param unknown_type $site_details
	 * @return Ambigous <unknown>
	 * @return int
	 */
	private function processSite($site_details) {
		$site_details = $this->array2hash($site_details);
		$elgg_site = get_entity($site_details['id']);
		if ($elgg_site) {
			$elgg_site->name = $site_details['name'];
			$elgg_site->url = $site_details['url'];
			$elgg_site->description = $site_details['description'];
			$elgg_site->email = $site_details['email'];
			$elgg_site->access_id = ACCESS_PUBLIC;
			$guid = $elgg_site->save();
		} else {
			$site = new ElggSite();
			$site->name = $site_details['name'];
			$site->url = $site_details['url'];
			$site->description = $site_details['description'];
			$site->email = $site_details['email'];
			$site->access_id = ACCESS_PUBLIC;
			$guid = $site->save();
		}
		return $site_details['id'];
	}
	
	/**
	 * @param unknown_type $array
	 * @return multitype:unknown
	 * @return mixed
	 */
	private function array2hash($array) {
	
		$result = array();
		foreach ($array as $element) {
			$result[$element['tag']] = $element['value'];
		}
		return $result;
	}
	
	/**
	 * Processes, resets and restores all plugin settings
	 * @param $plugins
	 * @return null
	 */
	private function processPlugins($plugins) {
		//var_dump($plugins);
		if (!$plugins) {
			return false;
		}
	
		$plugin_list = array ();
		$plugin_enabled = array ();
		$plugin_settings = array ();
		$order = 10;
	
		foreach ( $plugins as $plugin ) {
	
			$name = '';
			$enabled = false;
			$settings = array ();
	
			foreach ( $plugin ['value'] as $element ) {
				/* order taken from xml plugin order
				 if ($element['tag'] == 'order') {
				$order = $element['value'];
				}
				*/
				if ($element ['tag'] == 'name') {
					$name = $element ['value'];
				}
				if ($element ['tag'] == 'enabled') {
					if ($element ['value'] == 'true') {
						$enabled = true;
					}
				}
				if ($element ['tag'] == 'settings') {
					foreach ( $element ['value'] as $setting ) {
						$setting_name = false;
						$setting_value = false;
						foreach ( $setting ['value'] as $pair ) {
	
							if ($pair ['tag'] == 'name') {
								$setting_name = $pair ['value'];
							} else {
								// restore boolean values
								if ($pair ['value'] == 'true') {
									$setting_value = true;
								} else if ($pair ['value'] == 'false') {
									$setting_value = false;
								} else {
									$setting_value = $pair ['value'];
								}
							}
						}
						$settings [$setting_name] = $setting_value;
					}
				}
			}
			// save plugin settings to a general array
			if ($settings) {
				$plugin_settings [$name] = $settings;
			}
	
			// define the plugin order by the xml sequence
			$plugin_list [$order] = $name;
			$order = $order + 10;
	
			// get a list of plugins to enable
			if ($enabled) {
				array_push ( $plugin_enabled, $name );
			}
		}
	
		// disable all current plugins
		$this->disableAllPlugins ();
	
		// clear all plugin settings
		$this->clearAllPluginSettings ();
	
		// setup new plugin order
		regenerate_plugin_list ( $plugin_list );
	
		// enable plugins
		$this->enablePlugins ( $plugin_enabled );
	
		// restore settings to plugins
		$this->restorePluginSettings ( $plugin_settings );
	
	}
	
	/**
	 * Restores plugin settings
	 * @param $plugin_settings
	 * @return null
	 */
	private function restorePluginSettings($plugin_settings) {
		foreach ( $plugin_settings as $plugin => $settings ) {
			foreach ( $settings as $setting_name => $setting_value ) {
				elgg_set_plugin_setting ( $setting_name, $setting_value, $plugin );
			}
		}
	}
	
	/**
	 * Clears all plugin settings for all plugins
	 * @return null
	 */
	private function clearAllPluginSettings() {
		$plugins = elgg_get_plugins();
		if ($plugins) {
			foreach ( $plugins as $plugin ) {
				remove_all_private_settings ( $plugin->guid );
			}
		}
	}
	
	/**
	 * Takes an array of plugins to quickly enable the plugins in the array
	 * which is ALOT faster than using enable_plugin for each plugin
	 * @param $plugin_enabled
	 * @return null
	 */
	private function enablePlugins($plugin_enabled) {
		global $CONFIG;
		$site_guid = $CONFIG->site_guid;
		$site = get_entity ( $site_guid );
		array_unique ( $plugin_enabled );
		$site->setMetaData ( 'enabled_plugins', $plugin_enabled );
	}
	
	
	/**
	 * Disables all plugins, but keeps one in due to a elgg bug
	 * @return null
	 */
	private function disableAllPlugins() {
		global $CONFIG;
		$site_guid = $CONFIG->site_guid;
		$site = get_entity ( $site_guid );
		$site->setMetaData ( 'enabled_plugins', array ('updateclient' ) );
	}
	
	/**
	 * Really couldn't be asked to write my own xml parser, so I googled for one.
	 * @see http://uk2.php.net/manual/en/class.xmlreader.php (PxL comment)
	 * @param string $xml
	 * @return NULL
	 * @todo use 1.9 ElggXmlElement
	 */
	private function xml2assoc($xml) {
		$tree = null;
		while ( $xml->read () )
			switch ($xml->nodeType) {
				case XMLReader::END_ELEMENT :
					return $tree;
				case XMLReader::ELEMENT :
					$node = array ('tag' => $xml->name, 'value' => $xml->isEmptyElement ? '' : $this->xml2assoc ( $xml ) );
					if ($xml->hasAttributes)
						while ( $xml->moveToNextAttribute () )
						$node ['attributes'] [$xml->name] = $xml->value;
					$tree [] = $node;
					break;
				case XMLReader::TEXT :
				case XMLReader::CDATA :
					$tree .= $xml->value;
		}
		return $tree;
	}
}