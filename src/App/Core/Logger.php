<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    const string FILE = 'log';

    public static function info(string $message, array $array = []): void
    {
        $message = sprintf('%s: %s', $message, json_encode($array));
        self::log($message);
    }

    public static function error(array $array = []): void
    {
        $message = sprintf('%s: %s', 'Error', json_encode($array));
        self::log($message);
    }

    private static function log(string $message): void
    {
        $logEntry = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;

        file_put_contents(self::FILE, $logEntry, FILE_APPEND);
    }
}
