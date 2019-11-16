<?php
class Logger
{
    private static $toHide = [];

    public static function hide($channel)
    {
        self::log("logger", "Hiding channel $channel from logs!");
        self::$toHide[] = $channel;
    }

    public static function log($channel, $text)
    {
        if (in_array($channel, self::$toHide)) {
            return;
        }
        printf("[%s](%s) %s".PHP_EOL, date("H:i:s"), str_pad($channel, 15, ' ', STR_PAD_BOTH), $text);
    }
}
