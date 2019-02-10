<?php

declare(strict_types=1);
/**
 * Module to work with SwiftMailer.
 */
namespace Siler\SwiftMailer;

use Siler\Container;

const SWIFT_MAILER = 'swift_mailer';

/**
 * Send a Swift_Message using the Mailer in the Container.
 *
 * @param \Swift_Message $message
 *
 * @return mixed
 */
function send(\Swift_Message $message)
{
    if (!Container\has(SWIFT_MAILER)) {
        throw new \UnderflowException('You should call mailer() before send()');
    }

    $mailer = Container\get(SWIFT_MAILER);
    return $mailer->send($message);
}

/**
 * Sugar to create a new SwiftMailer Message.
 *
 * @param string $subject
 * @param array $from
 * @param array $to
 * @param string $body
 * @param string|null $contentType
 *
 * @return \Swift_Message
 */
function message(string $subject, array $from, array $to, string $body, ?string $contentType = null): \Swift_Message
{
    return (new \Swift_Message())
        ->setSubject($subject)
        ->setFrom($from)
        ->setTo($to)
        ->setBody($body, $contentType);
}

/**
 * Sugar to create a new SwiftMailer SMTP transport.
 *
 * @param string $host
 * @param int $port
 * @param string|null $username
 * @param string|null $password
 *
 * @return \Swift_Transport
 */
function smtp(string $host, int $port, ?string $username = null, ?string $password = null): \Swift_Transport
{
    $transport = new \Swift_SmtpTransport($host, $port);

    if (!is_null($username)) {
        $transport->setUsername($username);
    }

    if (!is_null($password)) {
        $transport->setPassword($password);
    }

    return $transport;
}

/**
 * Setup a Swift Mailer in the Siler Container.
 *
 * @param \Swift_Transport $transport
 *
 * @return \Swift_Mailer
 */
function mailer(\Swift_Transport $transport): \Swift_Mailer
{
    $mailer = new \Swift_Mailer($transport);
    Container\set(SWIFT_MAILER, $mailer);
    return $mailer;
}
