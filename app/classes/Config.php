<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 * Package:   Class for managing website configuration
 */

namespace App\Classes;

class Config extends Core {
	/**
	 * Update configuration
	 */
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

	/**
	 * Update .env configuration
	 */
	public function updateConfigFile($data) {
		if (!empty($data)) {
			$file = file(APPDIR . '.env');

			foreach ($file as $key => $value) {
				if (!empty($value)) {
					$current = explode('=', $value);
					if (isset($data[$current[0]])) $file[$key] = $current[0] . '=' . $data[$current[0]] . "\r\n";
				}
			}

			// Save data
			file_put_contents(APPDIR . '.env', $file);
		}
	}

	/**
	 * Update main website configuration
	 *
	 * @param array $data
	 * @return void
	 */
	public function updateConfigData($data)
	{
		foreach ($data as $key => $value) {
			$this->db->update('settings', array('value'), array($value), "setting_name = '{$key}'");
		}
	}
}