<?php

declare(strict_types=1);

namespace Siler\Test\Unit\HttpHandlerRunner;

use PHPUnit\Framework\TestCase;
use Siler\HttpHandlerRunner;
use Siler\Diactoros;

class HttpHandlerRunnerTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSapiEmit()
    {
        $response = Diactoros\json(['foo' => 'bar']);

        $this->expectOutputString('{"foo":"bar"}');

        $result = HttpHandlerRunner\sapi_emit($response);

        $this->assertTrue($result);
    }
}
