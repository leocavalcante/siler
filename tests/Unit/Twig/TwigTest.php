<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\Twig;

class TwigTest extends TestCase
{


    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Twig should be initialized first
     */
    public function testRenderWithoutInit()
    {
        Container\set('twig', null);
        Twig\render('template.twig');
    }


    public function testCreateTwigEnv()
    {
        $twigEnv = Twig\init(__DIR__ . '/../../fixtures');
        $this->assertInstanceOf(\Twig_Environment::class, $twigEnv);
    }


    public function testRender()
    {
        Twig\init(__DIR__ . '/../../fixtures');
        $this->assertSame('<p>bar</p>', Twig\render('template.twig', ['foo' => 'bar']));
    }
}
