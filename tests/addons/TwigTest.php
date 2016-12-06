<?php

use PHPUnit\Framework\TestCase;

class TwigTest extends TestCase
{
    public function testCreateTwigEnv()
    {
        $twigEnv = create_twig_env(__DIR__);
        $this->assertInstanceOf(\Twig_Environment::class, $twigEnv);
    }

    public function testRender()
    {
        create_twig_env(__DIR__);
        $this->assertEquals("<p>bar</p>\n", render('test.twig', ['foo' => 'bar']));
    }
}
