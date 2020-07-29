<?php
/*
* (c) Aleksandar Vranešević
* Author:    Aleksandar Vranešević
* URI:       https://vavok.net
* Class for managing website configuration
* Updated:   29.07.2020. 17:09:22
*/

class Config {

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

	public function update_config_data($data) {
		global $db;

		$fields = array(); $values = array();

		foreach ($data as $key => $value) {
			$fields[] .= $key;
			$values[] .= $value;
		}

		$db->update(DB_PREFIX . 'settings', $fields, $values);

	}

}

?>