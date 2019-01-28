<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Siler\Monolog as Log;

class MonologTest extends TestCase
{
    public function testStream()
    {
        $handler = Log\stream('php://output');
        $this->assertInstanceOf(StreamHandler::class, $handler);
    }

    public function testLog()
    {
        $handler = new TestHandler();

        Log\handler($handler);
        Log\log(Logger::WARNING, 'test');

        list($record) = $handler->getRecords();

        $this->assertSame(Log\MONOLOG_DEFAULT_CHANNEL, $record['channel']);
    }

    public function testSugar()
    {
        $handler =  new TestHandler();
        Log\handler($handler, 'test');

        $levels = array_values(Logger::getLevels());

        foreach ($levels as $level) {
            $levelName = strtolower(Logger::getLevelName($level));
            call_user_func("Siler\\Monolog\\$levelName", $levelName, ['context' => $levelName], 'test');
        }

        $records = $handler->getRecords();

        foreach ($levels as $i => $level) {
            $levelName = Logger::getLevelName($level);
            $record = $records[$i];

            $this->assertSame(strtolower($levelName), $record['message']);
            $this->assertSame($level, $record['level']);
            $this->assertSame($levelName, $record['level_name']);
            $this->assertArraySubset(['context' => strtolower($levelName)], $record['context']);
            $this->assertSame('test', $record['channel']);
        }
    }
}

