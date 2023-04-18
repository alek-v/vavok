<?php
/**
 * Author: Aleksandar Vranešević
 * Site:   https://vavok.net
 */

namespace App\Classes;

use App\Traits\Core;
use Pimple\Container;

abstract class BaseModel {
    use Core;

    protected Database $db;
    protected User $user;
    protected Localization $localization;
    protected Config $configuration;
    // Set default values for the content of the page, this values can be extended or changed
    protected array $page_data = [
        'content' => '',
        'head_tags' => ''
    ];

    public function __construct(protected Container $container)
    {
        $this->db = $this->container['db'];
        $this->user = $this->container['user'];
        $this->localization = $this->container['localization'];
        $this->configuration = $this->container['config'];
    }
}