<?php

declare(strict_types=1);

namespace faz\common;

class Debug {

    public static function dump($obj): void {
        ob_start();
        var_dump($obj);
        $dump = ob_get_clean();
        echo $dump;
    }

    public static function spaceDump($obj) : void {
        echo "\n-----------------------\n";
        self::dump($obj);
        echo "\n-----------------------\n";
    }

    public static function dumpF(callable $f): void {
        ob_start();
        $f();
        $dump = ob_get_clean();
        echo $dump;
    }

    public static function memoryF(callable $f): void {
        $start = memory_get_usage();
        $f();
        $end = memory_get_usage();
        echo "Memory usage: " . ($end - $start) . " bytes\n";
    }
}