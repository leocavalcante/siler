<?php

namespace Siler\Test\Integration;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Siler\Dotenv;
use Siler\Jwt;
use function Siler\Dotenv\env;

class JwtAuthTest extends \PHPUnit\Framework\TestCase
{
    protected static $cookies;

    public static function setUpBeforeClass()
    {
        self::$cookies = [];
    }

    public function testSignUp()
    {
        $jwtConfig = Jwt\conf('https://example.org', 'https://example.net', uniqid(), time(), time(), time() + 3600);
        $jwtBuilder = Jwt\builder($jwtConfig, new Sha256(), 'test');

        $token = $jwtBuilder(['uid' => 1]);

        self::$cookies['jwt'] = (string) $token;

        $this->assertEquals('https://example.org', $token->getClaim('iss'));
        $this->assertEquals(1, $token->getClaim('uid'));
    }

    /**
     * @depends testSignUp
     */
    public function testSignIn()
    {
        $token = self::$cookies['jwt'];
        $token = Jwt\parse($token);
        $validator = Jwt\validator(Jwt\conf('https://example.org', 'https://example.net'), time());

        $this->assertTrue($validator($token));
        $this->assertTrue($token->verify(new Sha256(), 'test'));

        $this->assertEquals(1, $token->getClaim('uid'));
    }
}
