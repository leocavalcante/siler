<?php

namespace Siler\Test\Unit;

use Lcobucci\JWT\Signer\Hmac\Sha256;
use Siler\Jwt;

class JwtTest extends \PHPUnit\Framework\TestCase
{
    const ISS = 'https://example.com';
    const AUD = 'https://example.org';
    const JTI = 'test123';
    const IAT = '10000';
    const NBF = '10060';
    const EXP = '13600';
    const KEY = 'test';

    protected $config;
    protected $data;
    protected $signer;

    protected function setUp()
    {
        $this->config = [
            'iss' => self::ISS,
            'aud' => self::AUD,
            'jti' => self::JTI,
            'iat' => self::IAT,
            'nbf' => self::NBF,
            'exp' => self::EXP,
        ];

        $this->data = [
            'uid' => 1,
        ];

        $this->signer = new Sha256();
    }

    public function testBuilder()
    {
        $token = Jwt\builder($this->config)($this->data);

        $this->assertSame(self::JTI, $token->getHeader('jti'));

        $this->assertSame(self::ISS, $token->getClaim('iss'));
        $this->assertSame(self::AUD, $token->getClaim('aud'));
        $this->assertEquals(self::IAT, $token->getClaim('iat'));
        $this->assertEquals(self::NBF, $token->getClaim('nbf'));
        $this->assertEquals(self::EXP, $token->getClaim('exp'));

        $this->assertSame(1, $token->getClaim('uid'));

        $this->assertStringEqualsFile(__DIR__.'/../../fixtures/jwt_unsigned.txt', $token);
    }

    public function testValidate()
    {
        $token = Jwt\builder($this->config)($this->data);

        $this->assertFalse(Jwt\validator($this->config, self::IAT)($token));
        $this->assertTrue(Jwt\validator($this->config, self::NBF)($token));
        $this->assertTrue(Jwt\validator($this->config, self::EXP)($token));
        $this->assertFalse(Jwt\validator($this->config, self::EXP + 1)($token));
    }

    public function testBuilderWithSigner()
    {
        $token = Jwt\builder($this->config, $this->signer, self::KEY)($this->data);

        $this->assertStringEqualsFile(__DIR__.'/../../fixtures/jwt_signed.txt', $token);

        $this->assertTrue($token->verify($this->signer, self::KEY));
        $this->assertFalse($token->verify($this->signer, 'wrong key'));
    }

    public function testParse()
    {
        $token = Jwt\parse(file_get_contents(__DIR__.'/../../fixtures/jwt_signed.txt'));

        $this->assertSame(self::JTI, $token->getHeader('jti'));

        $this->assertSame(self::ISS, $token->getClaim('iss'));
        $this->assertSame(self::AUD, $token->getClaim('aud'));
        $this->assertEquals(self::IAT, $token->getClaim('iat'));
        $this->assertEquals(self::NBF, $token->getClaim('nbf'));
        $this->assertEquals(self::EXP, $token->getClaim('exp'));

        $this->assertSame(1, $token->getClaim('uid'));

        $this->assertTrue($token->verify($this->signer, self::KEY));
    }

    public function testConf()
    {
        $config = Jwt\conf(self::ISS, self::AUD, self::JTI, self::IAT, self::NBF, self::EXP);
        $this->assertSame($this->config, $config);
    }
}
