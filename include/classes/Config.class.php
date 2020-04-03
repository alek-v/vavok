<?php
// (c) vavok.net - Aleksandar Vranesevic
// class for managing website configuration

class Config {

	function __construct() {
		$this->conf_file = '../used/config.dat';
	    $this->config = explode('|', file_get_contents($this->conf_file));
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


}
?>