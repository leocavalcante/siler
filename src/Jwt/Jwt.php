<?php
/**
 * Helper functions for lcobucci/jwt library.
 */

namespace Siler\Jwt;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

/**
 * Returns a factory function to build new tokens based on the given $config.
 *
 * @param array       $config
 * @param Signer|null $signer
 * @param string      $key
 *
 * @return callable
 */
function builder(array $config, Signer $signer = null, $key = null)
{
    $builder = new Builder();

    if (isset($config['iss'])) {
        $builder->setIssuer($config['iss']);
    }

    if (isset($config['aud'])) {
        $builder->setAudience($config['aud']);
    }

    if (isset($config['jti'])) {
        $builder->setId($config['jti'], true);
    }

    if (isset($config['iat'])) {
        $builder->setIssuedAt($config['iat']);
    }

    if (isset($config['nbf'])) {
        $builder->setNotBefore($config['nbf']);
    }

    if (isset($config['exp'])) {
        $builder->setExpiration($config['exp']);
    }

    return function (array $data) use ($builder, $signer, $key) {
        foreach ($data as $claim => $value) {
            $builder->set($claim, $value);
        }

        if (!is_null($signer) && !is_null($key)) {
            $builder->sign($signer, $key);
        }

        return $builder->getToken();
    };
}

/**
 * Returns a validation function based on the given $config and $time.
 *
 * @param array  $config
 * @param string $time
 *
 * @return callable
 */
function validator(array $config, $time)
{
    $data = new ValidationData();
    $data->setCurrentTime($time);

    if (isset($config['iss'])) {
        $data->setIssuer($config['iss']);
    }

    if (isset($config['aud'])) {
        $data->setAudience($config['aud']);
    }

    if (isset($config['jti'])) {
        $data->setId($config['jti']);
    }

    return function (Token $token) use ($data) {
        return $token->validate($data);
    };
}

/**
 * Parser helper.
 *
 * @param string $token
 *
 * @return Token
 */
function parse($token)
{
    return (new Parser())->parse($token);
}

/**
 * Configuration helper.
 *
 * @param string $iss configures the issuer (iss claim)
 * @param string $aud configures the audience (aud claim)
 * @param string $jti configures the id (jti claim), replicating as a header item
 * @param string $iat configures the time that the token was issue (iat claim)
 * @param string $nbf configures the time that the token can be used (nbf claim)
 * @param string $exp configures the expiration time of the token (nbf claim)
 *
 * @return array
 */
function conf($iss = null, $aud = null, $jti = null, $iat = null, $nbf = null, $exp = null)
{
    return compact('iss', 'aud', 'jti', 'iat', 'nbf', 'exp');
}
