<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Siler\Container;
use Siler\Twig;
use Twig\Environment;

class TwigTest extends TestCase
{
    public function testRenderWithoutInit()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Twig should be initialized first');

        Container\set('twig', null);
        Twig\render('template.twig');
    }

    public function testCreateTwigEnv()
    {
        $twigEnv = Twig\init(__DIR__ . '/../../fixtures');
        $this->assertInstanceOf(Environment::class, $twigEnv);
    }

    public function testRender()
    {
        Twig\init(__DIR__ . '/../../fixtures');
        $this->assertSame('<p>bar</p>', Twig\render('template.twig', ['foo' => 'bar']));
    }
}
