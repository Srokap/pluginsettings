<?php
/**
 * Contains functions used for exporting
 * @todo limit elements by configuration
 */
class PluginSettingsExporter {
	
	/**
	 * Timestamp of the export execution start
	 * @var int
	 */
	protected $time;
	
	/**
	 * @var string
	 */
	protected $fileName;
	
	/**
	 * @var string
	 */
	protected $result;
	
	/**
	 * @var int
	 */
	const T_STRING = 0;
	
	/**
	 * @var int
	 */
	const T_BOOLEAN = 1;
	
	/**
	 * @var int
	 */
	const T_ACCESS = 2;
	
	/**
	 * @var int
	 */
	const T_ARRAY = 3;
	
	/**
	 * @return string
	 */
	public function execute() {
		$this->time = time();
		
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->setIndent(true);
		
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement('elgg');
		
		$xml->writeAttribute('timestamp', $this->time );
		$xml->writeAttribute('version', get_version(false));
		$xml->writeAttribute('release', get_version(true));
		
		$this->exportCore($xml);
		$this->exportPlugins($xml);
		
		$xml->endElement();// end the elgg root element
		
		$this->result = utf8_encode ( $xml->outputMemory ( true ) );
		unset($xml);
	}
	
	/**
	 * Outputs result to stdout
	 */
	public function output() {
		// serve xml doc as xml
		header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');
		header('Content-type: application/xml');
		
		echo $this->getResult();
	}
	
	/**
	 * @return number
	 */
	public function getTime() {
		return $this->time;
	}
	
	/**
	 * @return string
	 */
	public function getFileName() {
		if (!isset($this->fileName)) {
			$this->fileName = 'site-export-' . gmdate('Y-m-d-H-i-s', $this->time) . '.xml';
		}
		return $this->fileName;
	}
	
	/**
	 * @param string $val
	 */
	public function setFileName($val) {
		$this->fileName = $val;
	}
	
	/**
	 * @return string
	 */
	public function getResult() {
		return $this->result;
	}
	
	/**
	 * @param XMLWriter $xml
	 */
	protected function exportCore(XMLWriter $xml) {
		// used to save core settings
		$xml->startElement('core');
		$this->exportCoreDatabase($xml);
		$this->exportCoreConfig($xml);
		$this->exportCoreSite($xml);
		$xml->endElement (); // end core
	}
	
	/**
	 * @param XMLWriter $xml
	 */
	protected function exportPlugins(XMLWriter $xml) {
		// get plugin information
		$plugin_list = elgg_get_plugin_ids_in_dir();
		$priorityName = elgg_namespace_plugin_private_setting('internal', 'priority');
		
		if ($plugin_list) {
			$xml->startElement ( 'plugins' );
		
			foreach ( $plugin_list as $order => $pluginId ) {
				// start plugin element
				$xml->startElement ( 'plugin' );
		
				$xml->startElement ( 'name' );
				$xml->text ( htmlentities ( $pluginId ) );
				$xml->endElement ();
		
				$xml->startElement ( 'enabled' );
				if (elgg_is_active_plugin($pluginId)) {
					$xml->text ( 'true' );
				} else {
					$xml->text ( 'false' );
				}
				$xml->endElement ();
		
				$plugin_entity = elgg_get_plugin_from_id($pluginId);
				if ($plugin_entity instanceof ElggPlugin) {
					$xml->startElement('order');
					$xml->text($plugin_entity->getPrivateSetting($priorityName));
					$xml->endElement();
					
					$plugin_settings = get_all_private_settings ( $plugin_entity->guid );
					if ($plugin_settings) {
		
						$xml->startElement ( 'settings' );
		
						foreach ( $plugin_settings as $name => $value ) {
							$xml->startElement ( 'setting' );
		
							$xml->startElement ( 'name' );
							$xml->text ( htmlentities ( $name ) );
							$xml->endElement ();
		
							$xml->startElement ( 'value' );
							if (is_bool ( $value )) {
								if ($value) {
									$value = 'true';
								} else {
									$value = 'false';
								}
								$xml->text ( $value );
							} else {
								$value = htmlentities ( $value );
								if (preg_match ( '/\n/', $value )) {
		
									$xml->writeCData ( $value );
									$value = "\n<![CDATA[\n" . $value . "\n]]>\n";
								} else {
									$xml->text ( $value );
								}
							}
							// end value element
							$xml->endElement ();
		
							// end setting element
							$xml->endElement ();
						}
		
						// end settings element
						$xml->endElement ();
					}
		
				}
		
				// end plugin element
				$xml->endElement ();
			}
			// end plugins element
			$xml->endElement ();
		}
	}
	
	/**
	 * @param XMLWriter $xml
	 */
	protected function exportCoreSite(XMLWriter $xml) {
		global $CONFIG;
		
		$xml->startElement ( 'site' );
		
		$xml->startElement ( 'id' );
		$xml->text ( $CONFIG->site_id );
		$xml->endElement ();
		
		$xml->startElement ( 'name' );
		$xml->text ( $CONFIG->sitename );
		$xml->endElement ();
		
		$xml->startElement ( 'description' );
		$xml->text ( $CONFIG->sitedescription );
		$xml->endElement ();
		
		$xml->startElement ( 'url' );
		$xml->text ( $CONFIG->wwwroot );
		$xml->endElement ();
		
		$xml->startElement ( 'email' );
		$xml->text ( $CONFIG->siteemail );
		$xml->endElement ();
		
		$xml->endElement (); // end site
	}
	
	/**
	 * @param mixed $var
	 * @return string
	 */
	private function getType($var) {
		if (is_bool($var)) {
			return self::T_BOOLEAN;
		} elseif (is_array($var)) {
			return self::T_ARRAY;
		} else {
			return self::T_STRING;
		}
	}
	
	private function addElement($xml, $name, $value, $type) {
		$xml->startElement($name);
		switch ($type) {
			case self::T_BOOLEAN:
				$xml->text ( $value ? 'true' : 'false' );
				break;
			case self::T_ACCESS:
				switch ($value) {
					case ACCESS_DEFAULT :
						$xml->text('ACCESS_DEFAULT');
						break;
					case ACCESS_PRIVATE :
						$xml->text('ACCESS_PRIVATE');
						break;
					case ACCESS_LOGGED_IN :
						$xml->text('ACCESS_LOGGED_IN');
						break;
					case ACCESS_PUBLIC :
						$xml->text('ACCESS_PUBLIC');
						break;
					case ACCESS_FRIENDS :
						$xml->text('ACCESS_FRIENDS');
						break;
					default:
						$xml->text($value);
						break;
				}
				break;
			case self::T_ARRAY:
				foreach ($value as $item) {
					$this->addElement($xml, 'item', $item, $this->getType($item));
				}
				break;
			case self::T_STRING:
			default:
				$xml->text($value);
				break;
		}
		$xml->endElement();
	}
	
	/**
	 * @param XMLWriter $xml
	 * @param array $types
	 * @param callback $getter
	 */
	private function addElementsByConfig($xml, $types, $getter = 'elgg_get_config') {
		
		foreach ($types as $name => $type) {
			$value = call_user_func($getter, $name);
			$this->addElement($xml, $name, $value, $type);
		}
	}
	
	/**
	 * @param XMLWriter $xml
	 */
	protected function exportCoreConfig(XMLWriter $xml) {
		
		$xml->startElement ( 'config' );

		//TODO consider just dumping tables
		
		//datalists table
		$typesDatalists = array(
			'path' => self::T_STRING, 
			'dataroot' => self::T_STRING, 
			'default_site' => self::T_STRING, 
			'simplecache_enabled' => self::T_BOOLEAN, 
			'system_cache_enabled' => self::T_BOOLEAN, 
			'__site_secret__' => self::T_STRING, 
			'debug' => self::T_STRING, 
			'https_login' => self::T_BOOLEAN, 
			'enable_api' => self::T_BOOLEAN,
		);
		$this->addElementsByConfig($xml, $typesDatalists, 'datalist_get');
		
		//config table
		$typesConfig = array(
			'view' => self::T_STRING,
			'language' => self::T_STRING,
			'default_access' => self::T_ACCESS,
			'allow_registration' => self::T_BOOLEAN,
			'walled_garden' => self::T_BOOLEAN,
			'allow_user_default_access' => self::T_BOOLEAN,
			//settings.php file
			'memcache' => self::T_BOOLEAN,
			'memcache_servers' => self::T_ARRAY,
			'broken_mta' => self::T_BOOLEAN,
			'db_disable_query_cache' => self::T_BOOLEAN,
			'min_password_length' => self::T_STRING,
		);
		$this->addElementsByConfig($xml, $typesConfig, 'elgg_get_config');
		
		$xml->endElement (); // end config
	}	
	
	/**
	 * @param XMLWriter $xml
	 * @param string $dblinkname
	 * @param object $connection
	 */
	private function addDbConnection(XMLWriter $xml, $dblinkname, $connection) {
		$xml->startElement ( 'connection' );
		$xml->writeAttribute ( 'type', $dblinkname );
		
		$xml->startElement ( 'username' );
		$xml->text ( $connection->dbuser );
		$xml->endElement ();
		
		$xml->startElement ( 'password' );
		$xml->text ( $connection->dbpass );
		$xml->endElement ();
		
		$xml->startElement ( 'database' );
		$xml->text ( $connection->dbname );
		$xml->endElement ();
		
		$xml->startElement ( 'host' );
		$xml->text ( $connection->dbhost );
		$xml->endElement ();
		
		$xml->endElement (); // end connection
	}
	
	/**
	 * @param XMLWriter $xml
	 */
	protected function exportCoreDatabase(XMLWriter $xml) {
		global $CONFIG;
		
		$xml->startElement ( 'database' );
		$xml->writeAttribute ( 'table_prefix', $CONFIG->dbprefix );
		$xml->writeAttribute ( 'split_connections', $CONFIG->db->split ? 'true' : 'false' );
		
		if (! empty ( $CONFIG->db->split )) {
			// handle multiple connections for reading and writing
			foreach ( array ('read', 'write' ) as $dblinkname ) {
				if (is_array ( $CONFIG->db [$dblinkname] )) {
					foreach ( $CONFIG->db [$dblinkname] as $connection ) {
						$this->addDbConnection($xml, $dblinkname, $connection);
					}
				} else {
					$this->addDbConnection($xml, $dblinkname, $CONFIG->db[$dblinkname]);
				}
			}
		} else {
			// dump the single read/write connection
			$this->addDbConnection($xml, 'readwrite', $CONFIG);
		}
		$xml->endElement (); // end database
	}
}