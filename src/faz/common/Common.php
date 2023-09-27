<?php

declare(strict_types=1);

namespace faz\common;

use pocketmine\plugin\PluginBase;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Common {

    public static function getConnector(PluginBase $pluginBase, array $sqlMap = ["sqlite" => "sqlite.sql", "mysql" => "mysql.sql"]) :DataConnector {
        $databaseConfig = $pluginBase->getConfig()->get("database");
        return libasynql::create($pluginBase, $databaseConfig, $sqlMap);
    }

}