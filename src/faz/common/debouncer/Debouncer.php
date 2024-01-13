<?php

declare(strict_types=1);

namespace faz\common\debouncer;

use Closure;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class Debouncer {

    private int $startTime;
    private string $prefix;
    public function __construct(string $prefix, private Closure $clousure, private int $delay, private PluginBase $pluginBase) {
        $this->startTime = time();
        $this->prefix = $prefix;
        $this->tick();
    }

    public function getStartTime() :int {
        return $this->startTime;
    }

    public function getPrefix() : string {
        return $this->prefix;
    }

    public function check() : bool {
        $currentTime = time();
        return $currentTime - $this->startTime >= $this->delay;
    }

    public function reset() : void {
        $this->startTime = time();
    }

    public function tick() : void {
        $classTask = new class($this) extends Task {
            public function __construct(private readonly Debouncer $debouncer) {}
            public function onRun() :void {
                if($this->debouncer->check()){
                    $this->debouncer->call();
                    DebouningPool::removeDebouncer($this->debouncer->getPrefix());
                    $this->getHandler()->cancel();
                }
            }
        };
        $this->pluginBase->getScheduler()->scheduleRepeatingTask($classTask, 20);
    }

    public function getDelay() :int {
        return $this->delay;
    }

    public function getClosure() :Closure {
        return $this->clousure;
    }

    public function setDelay(int $delay) :void {
        $this->delay = $delay;
    }

    public function setClosure(Closure $closure) :void {
        $this->clousure = $closure;
    }

    public function equals(Debouncer $debouncer) :bool {
        return $this->delay === $debouncer->getDelay() && $this->clousure === $debouncer->getClosure();
    }

    public function call() :void {
        ($this->clousure)();
    }

    public function __toString() :string {
        return "Debouncer(delay: {$this->delay}, closure: {$this->clousure})";
    }

    public static function create(string $prefix, PluginBase $pluginBase, Closure $closure, int $delay) :Debouncer {
        if(DebouningPool::hasDebouncer($prefix)){
            $debouncer = DebouningPool::getDebouncer($prefix);
            $debouncer->reset();
        }else {
            $debouncer = new Debouncer($prefix, $closure, $delay, $pluginBase);
            DebouningPool::addDebouncer($debouncer);
        }
        return $debouncer;
    }

}