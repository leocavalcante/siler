<?php

declare(strict_types=1);

namespace Siler\Test\Unit;

use PHPUnit\Framework\TestCase;
use Siler\Container;
use Siler\SwiftMailer as Mail;
use Swift_Mailer;
use Swift_Message;
use Swift_NullTransport;
use Swift_SmtpTransport;
use UnderflowException;

class SwiftMailerTest extends TestCase
{
    public function testSendWithoutMailer()
    {
        $this->expectException(UnderflowException::class);
        Mail\send(new Swift_Message());
    }

    public function testMailer()
    {
        $mailer = Mail\mailer(new Swift_NullTransport());
        $this->assertInstanceOf(Swift_Mailer::class, $mailer);
        $this->assertSame(Container\get(Mail\SWIFT_MAILER), $mailer);

        Mail\send(new Swift_Message());
    }

    public function testSugar()
    {
        $message = Mail\message('subject', ['from@from.from'], ['to@to.to'], 'body');
        $this->assertInstanceOf(Swift_Message::class, $message);
        $this->assertSame('subject', $message->getSubject());
        $this->assertSame('body', $message->getBody());
        $this->assertSame('text/plain', $message->getBodyContentType());
        $this->assertSame(['from@from.from' => null], $message->getFrom());
        $this->assertSame(['to@to.to' => null], $message->getTo());

        $smtp = Mail\smtp('host', 0, 'username', 'password');
        $this->assertInstanceOf(Swift_SmtpTransport::class, $smtp);
    }
}
