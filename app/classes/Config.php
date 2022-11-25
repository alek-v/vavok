<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;
use Pimple\Container;

class Config {
    protected object $db;

    public function __construct(protected Container $container)
    {
        $this->db = $container['db'];
    }

    /**
     * Update .env configuration
     * 
     * @return void
     */
    public function updateConfigFile(array $data): void
    {
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

    /**
     * Update main website configuration
     *
     * @param array $data
     * @return void
     */
    public function updateConfigData(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->db->update('settings', array('value'), array($value), "setting_name = '{$key}'");
        }
    }
}