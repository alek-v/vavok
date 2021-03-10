<?php
/**
 * Author:    Aleksandar Vranešević
 * URI:       https://vavok.net
 * Package:   Class for managing website configuration
 * Updated:   07.03.2021. 15:30:57
 */

class Config {
	private $vavok;

	public function __construct()
	{
		global $vavok;

		$this->vavok = $vavok;
	}

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

	/**
	 * Update main website configuration
	 *
	 * @param array $data
	 * @return void
	 */
	public function update_config_data($data)
	{
		foreach ($data as $key => $value) {
			$this->vavok->go('db')->update(DB_PREFIX . 'settings', array('value'), array($value), "setting_name = '{$key}'");
		}
	}
}

?>