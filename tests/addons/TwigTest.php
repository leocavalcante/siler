<?php

use PHPUnit\Framework\TestCase;

class TwigTest extends TestCase
{
    public function testCreateTwigEnv()
    {
        $twigEnv = create_twig_env(__DIR__, __DIR__);
        $this->assertInstanceOf(\Twig_Environment::class, $twigEnv);
    }
}
