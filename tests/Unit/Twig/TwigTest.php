<?php

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Twig;

class TwigTest extends TestCase
{
    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Twig should be initialized first
     */
    public function testRenderWithoutInit()
    {
        Twig\render('template.twig');
    }

    public function testCreateTwigEnv()
    {
        $twigEnv = Twig\init(__DIR__.'/../../fixtures');
        $this->assertInstanceOf(\Twig_Environment::class, $twigEnv);
    }

    public function testRender()
    {
        Twig\init(__DIR__.'/../../fixtures');
        $this->assertEquals("<p>bar</p>\n", Twig\render('template.twig', ['foo' => 'bar']));
    }
}
