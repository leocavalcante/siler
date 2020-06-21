<?php declare(strict_types=1);

namespace Siler\Monolog;

use Monolog\Logger;

/**
 * Internal DIC.
 *
 * @ignore Not part of the API.
 */
final class Loggers
{
    /** @var Logger[] */
    public static $loggers = [];

    /** @var array<int, bool|callable():bool> */
    public static $predicates = [];

    /**
     * Returns the Logger identified by the channel.
     *
     * @param string $channel The log channel.
     *
     * @return Logger
     */
    public static function getLogger(string $channel): Logger
    {
        if (empty(static::$loggers[$channel])) {
            static::$loggers[$channel] = new Logger($channel);
        }

        return static::$loggers[$channel];
    }

    /**
     * @param int $level
     * @param bool|callable():bool $predicate
     */
    public static function logIf(int $level, $predicate): void
    {
        static::$predicates[$level] = $predicate;
    }

    public static function gate(int $level): bool
    {
        if (!array_key_exists($level, self::$predicates)) {
            return true;
        }

        /** @var bool|callable():bool $pred */
        $pred = self::$predicates[$level];

        if (is_callable($pred)) {
            return $pred();
        }

        return $pred;
    }
}
