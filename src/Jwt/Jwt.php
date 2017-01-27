<?php
/**
 * Lcobucci\JWT helpers
 */

namespace Siler\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use Siler\Container;
use function Siler\array_get;

/**
 * Initialize a JWT environment
 *
 * @param array $config The JWT configuration
 */
function init(array $config, $key, Signer $signer = null)
{
    $builder = build_builder($config, new Builder());

    if (is_null($signer)) {
        $signer = new Sha256();
    }

    Container\set('jwt_builder', $builder);
    Container\set('jwt_signer', $signer);
    Container\set('jwt_key', $key);
}

/**
 * Returns a new token
 *
 * @throws RuntimeExeption if JWT is not initialized
 *
 * @return Lcobucci\JWT\Builder
 */
function token($nbf, $exp, array $claims = [], $iat = null)
{
    $builder = Container\get('jwt_builder');
    $signer = Container\get('jwt_signer');
    $key = Container\get('jwt_key');

    if (is_null($builder) || is_null($signer) || is_null($key)) {
        throw new \RuntimeException('JWT environment should be initialized first');
    }

    if (is_null($iat)) {
        $iat = time();
    }

    $builder = build_builder(compact('iat', 'nbf', 'exp'), $builder);

    foreach ($claims as $key => $value) {
        $builder = $builder->set($key, $value);
    }

    $builder = $builder->sign($signer, $key);

    return $builder->getToken();
}

function parse($token)
{
    return (new Parser())->parse((string) $token);
}

function verify($token)
{
    $signer = Container\get('jwt_signer');
    $key = Container\get('jwt_key');

    if (is_null($signer) || is_null($key)) {
        throw new \RuntimeException('JWT environment should be initialized first');
    }

    if (is_string($token)) {
        $token = parse($token);
    }

    return $token->verify($signer, $key);
}

function build_builder(array $config, Builder $builder)
{
    if (array_get($config, 'iss', false)) {
        $builder = $builder->setIssuer($config['iss']);
    }

    if (array_get($config, 'aud', false)) {
        if (is_array($config['aud'])) {
            foreach ($config['aud'] as $aud) {
                $builder = $builder->setAudience($aud);
            }
        } else {
            $builder = $builder->setAudience($config['aud']);
        }
    }

    if (array_get($config, 'jti', false)) {
        $builder = $builder->setId($config['jti'], true);
    }

    if (array_get($config, 'iat', false)) {
        $builder = $builder->setIssuedAt($config['iat']);
    }

    if (array_get($config, 'nbf', false)) {
        $builder = $builder->setNotBefore($config['nbf']);
    }

    if (array_get($config, 'exp', false)) {
        $builder = $builder->setExpiration($config['exp']);
    }

    return $builder;
}
