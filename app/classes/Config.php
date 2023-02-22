<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       https://vavok.net
 */

namespace App\Classes;

use Pimple\Container;

class Config {
    protected object $db;
    private array $settings;

    public function __construct(protected Container $container)
    {
        $this->db = $container['db'];

        foreach ($this->db->query("SELECT * FROM settings WHERE setting_group = 'system'") as $item) {
            $this->settings[$item['setting_name']] = $item['value'];
        }

        // Additional settings
        $this->settings['timeZone'] = empty($config['timeZone']) ? $config['timeZone'] = 0 : $config['timeZone']; // check is there timezone number set
        $this->settings['siteTime'] = time() + ($config['timeZone'] * 3600);
        $this->settings['homeBase'] = str_replace('https://', '', isset($config['homeUrl']) ? str_replace('http://', '', $config['homeUrl']) : '');
    }

    /**
     * Get website configuration
     *
     * @param string $data
     * @param bool $full_configuration
     * @return array|bool|string
     */
    public function getValue(string $data = '', bool $full_configuration = false): array|bool|string
    {
        if (!empty($data) && isset($this->settings[$data])) {
            return $this->settings[$data];
        }

        // Get complete configuration
        if ($full_configuration) {
            return $this->settings;
        }

        return false;
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