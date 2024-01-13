<?php

declare(strict_types=1);

namespace faz\common\debouncer;

class DebouningPool {

    /** @var Debouncer[] */
    private static array $debouncers = [];

    public static function addDebouncer(Debouncer $debouncer) :void {
        self::$debouncers[] = $debouncer;
    }

    public static function getDebouncer(string $name) : ?Debouncer {
        foreach(self::$debouncers as $debouncer){
            if($debouncer->getPrefix() == $name){
                return $debouncer;
            }
        }
        return null;
    }

    public static function removeDebouncer(string $name) : void {
        self::$debouncers = array_values(array_filter(self::$debouncers, function (Debouncer $debouncer) use ($name): bool {
            return $debouncer->getPrefix() !== $name;
        }));
    }

    public static function hasDebouncer(string $name) : bool {
        foreach(self::$debouncers as $debouncer){
            if($debouncer->getPrefix() == $name){
                return true;
            }
        }
        return false;
    }
}