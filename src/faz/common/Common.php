<?php

declare(strict_types=1);

namespace faz\common;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Common {

    public static function getConnector(PluginBase $pluginBase, array $sqlMap = ["sqlite" => "sqlite.sql", "mysql" => "mysql.sql"]) :DataConnector {
        $databaseConfig = $pluginBase->getConfig()->get("database");
        return libasynql::create($pluginBase, $databaseConfig, $sqlMap);
    }

    public static function getAllNamePlayers() : \Generator {
        $path = Server::getInstance()->getDataPath() . "players/";

        $playerFiles = glob($path . "*.dat");

        foreach ($playerFiles as $playerFile) {
            yield basename($playerFile, ".dat");
        }
    }

    public static function handleDelay(\Closure $closure, int $delay, PluginBase $plugin) :void {
        $taskClass = new class($closure) extends Task {
            private \Closure $closure;

            public function __construct(\Closure $closure) {
                $this->closure = $closure;
            }

            public function onRun() :void {
                ($this->closure)();
            }
        };
        $plugin->getScheduler()->scheduleDelayedTask($taskClass, $delay);
    }
}