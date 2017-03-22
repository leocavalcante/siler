<?php

namespace Siler\Test\Integration;

use Siler\Dotenv;
use function Siler\Dotenv\env;
use Siler\Jwt;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Siler\Http\Request;

class JwtAuthTest extends \PHPUnit\Framework\TestCase
{
    protected static $cookies;

    public static function setUpBeforeClass()
    {
        self::$cookies = [];
    }

    public function testSignUp()
    {
        Dotenv\init(__DIR__.'/../fixtures');

        $jwtConfig = Jwt\conf(env('JWT_ISS'), env('JWT_AUD'), uniqid(), time(), time(), time() + 3600);
        $jwtBuilder = Jwt\builder($jwtConfig, new Sha256(), env('APP_KEY'));

        $token = $jwtBuilder(['uid' => 1]);

        self::$cookies['jwt'] = (string) $token;

        $this->assertEquals(env('JWT_ISS'), $token->getClaim('iss'));
        $this->assertEquals(1, $token->getClaim('uid'));
    }

    /**
     * @depends testSignUp
     */
    public function testSignIn()
    {
        $token = self::$cookies['jwt'];

        $token = Jwt\parse($token);

        $this->assertTrue(Jwt\validator(Jwt\conf(env('JWT_ISS'), env('JWT_AUD')), time())($token));
        $this->assertTrue($token->verify(new Sha256(), env('APP_KEY')));

        $this->assertEquals(1, $token->getClaim('uid'));
    }
}
