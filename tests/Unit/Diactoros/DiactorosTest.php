<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Siler\Diactoros;

class DiactorosTest extends TestCase
{
    public function testRequest()
    {
        $this->assertInstanceOf(RequestInterface::class, Diactoros\request());
    }

    public function testResponse()
    {
        $this->assertInstanceOf(ResponseInterface::class, Diactoros\response());
    }

    public function testHtml()
    {
        $this->assertInstanceOf(HtmlResponse::class, Diactoros\html('test'));
    }

    public function testJson()
    {
        $this->assertInstanceOf(JsonResponse::class, Diactoros\json('test'));
    }

    public function testText()
    {
        $this->assertInstanceOf(TextResponse::class, Diactoros\text('test'));
    }

    public function testNone()
    {
        $this->assertInstanceOf(EmptyResponse::class, Diactoros\none());
    }

    public function testRedirect()
    {
        $this->assertInstanceOf(RedirectResponse::class, Diactoros\redirect('test'));
    }
}
