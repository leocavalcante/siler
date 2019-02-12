<?php

declare(strict_types=1);

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
        Dotenv\init(__DIR__ . '/../fixtures');

        $jwtConfig  = Jwt\conf(env('JWT_ISS'), env('JWT_AUD'), uniqid(), strval(time()), strval(time()), strval(time() + 3600));
        $jwtBuilder = Jwt\builder($jwtConfig, new Sha256(), env('APP_KEY'));

        $token = $jwtBuilder(['uid' => 1]);

        self::$cookies['jwt'] = (string) $token;

        $this->assertSame(env('JWT_ISS'), $token->getClaim('iss'));
        $this->assertSame(1, $token->getClaim('uid'));
    }


    /**
     * @depends testSignUp
     */
    public function testSignIn()
    {
        $token = self::$cookies['jwt'];

        $token = Jwt\parse($token);

        $this->assertTrue(Jwt\validator(Jwt\conf(env('JWT_ISS'), env('JWT_AUD')), strval(time()))($token));
        $this->assertTrue($token->verify(new Sha256(), env('APP_KEY')));

        $this->assertSame(1, $token->getClaim('uid'));
    }
}//end class
