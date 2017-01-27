<?php

namespace Siler\Test;

use PHPUnit\Framework\TestCase;
use Siler\Jwt;
use Siler\Container;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;

class JwtTest extends TestCase
{
    const KEY = 'secret';
    const TOKEN_STR = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImppdCI6InRlc3QifQ.eyJpc3MiOiJodHRwOi8vZXhhbXBsZS5jb20iLCJhdWQiOiJodHRwOi8vZXhhbXBsZS5vcmciLCJqdGkiOiJ0ZXN0IiwiaWF0IjoiMTQ4NTU1MjM5NyIsIm5iZiI6IjE0ODU1NTI0NzUiLCJleHAiOiIxNDg1NTU2MDI2IiwidWlkIjoiMSJ9.knIPz7fsI7HXLnGgaaTMPlQSi0I-mE1shIqQFFBOAs0';

    private $config;

    public function setUp()
    {
        $this->config = [
            'iss' => 'http://example.com',
            'aud' => 'http://example.org',
            'jti' => 'test',
            'iat' => '1485552397',
            'nbf' => '1485552475',
            'exp' => '1485556026',
        ];
    }

    public function testBuildBuilder()
    {
        $builder = Jwt\build_builder($this->config, new Builder());
        $token = $builder->getToken();

        $this->assertEquals($this->config['iss'], $token->getClaim('iss'));
        $this->assertEquals($this->config['aud'], $token->getClaim('aud'));
        $this->assertEquals($this->config['jti'], $token->getClaim('jti'));
        $this->assertEquals($this->config['jti'], $token->getHeader('jti'));
        $this->assertEquals($this->config['iat'], $token->getClaim('iat'));
        $this->assertEquals($this->config['nbf'], $token->getClaim('nbf'));
        $this->assertEquals($this->config['exp'], $token->getClaim('exp'));
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage JWT environment should be initialized first
     */
    public function testTokenShouldNotBeCalledWithoutInit()
    {
        Jwt\token('', '');
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage JWT environment should be initialized first
     */
    public function testVerifyShouldNotBeCalledWithoutInit()
    {
        Jwt\verify('');
    }

    public function testInit()
    {
        Jwt\init($this->config, self::KEY);

        $builder = Container\get('jwt_builder');
        $signer = Container\get('jwt_signer');
        $key = Container\get('jwt_key');

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertInstanceOf(Signer::class, $signer);
        $this->assertEquals(self::KEY, $key);
    }

    public function testTokenAndVerify()
    {
        $nbf = time();
        $exp = time() + 3600;

        $token = Jwt\token($nbf, $exp, ['uid' => 1], $this->config['iat']);

        $this->assertEquals($nbf, $token->getClaim('nbf'));
        $this->assertEquals($exp, $token->getClaim('exp'));
        $this->assertEquals(1, $token->getClaim('uid'));

        // $this->assertTrue(Jwt\verify($token));
    }

    public function testParse()
    {
        $token = Jwt\parse(self::TOKEN_STR);

        $this->assertEquals($this->config['iss'], $token->getClaim('iss'));
        $this->assertEquals($this->config['aud'], $token->getClaim('aud'));
        $this->assertEquals($this->config['jti'], $token->getClaim('jti'));
        $this->assertEquals($this->config['iat'], $token->getClaim('iat'));
        $this->assertEquals($this->config['nbf'], $token->getClaim('nbf'));
        $this->assertEquals($this->config['exp'], $token->getClaim('exp'));
    }
}
