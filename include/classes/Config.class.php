<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for managing website configuration
 * Updated:   02.09.2020. 22:26:50
 */

class Config {
	private $vavok;

	public function __construct()
	{
		global $vavok;

		$this->vavok = $vavok;
	}

	// update configuration
	function update($data) {
		// load configuration
	    $udata = $this->config;

	    // owerwrite configuration value
	    $i = 0;
	    foreach ($udata as $key => $val) {
	    	$udata[$i] = $val . '|';

	    	if (isset($data[$i])) {
	    		$udata[$i] = $data[$i] . '|';
	    	}

	    	$i++;
		}

	    // update file
	    file_put_contents($this->conf_file, $udata);

	    return true;
	}

	// Update .env configuration
	public function update_config_file($data) {
		if (!empty($data)) {
			$file = file(BASEDIR . '.env');

			foreach ($file as $key => $value) {
				if (!empty($value)) {
					$current = explode('=', $value);
					if (isset($data[$current[0]])) $file[$key] = $current[0] . '=' . $data[$current[0]] . "\r\n";
				}
			}

			// Save data
			file_put_contents(BASEDIR . '.env', $file);

		}
	}

	public function update_config_data($data)
	{
		$fields = array(); $values = array();

		foreach ($data as $key => $value) {
			$fields[] .= $key;
			$values[] .= $value;
		}

		$this->vavok->go('db')->update(DB_PREFIX . 'settings', $fields, $values);

	}

}

?>