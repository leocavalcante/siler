<?php

use PHPUnit\Framework\TestCase;

class TwigTest extends TestCase
{
    public function testCreateTwigEnv()
    {
        $twigEnv = Siler\Twig\init(__DIR__.'/../fixtures');
        $this->assertInstanceOf(\Twig_Environment::class, $twigEnv);
    }

    public function testRender()
    {
        Siler\Twig\init(__DIR__.'/../fixtures');
        $this->assertEquals("<p>bar</p>\n", Siler\Twig\render('template.twig', ['foo' => 'bar']));
    }
}
