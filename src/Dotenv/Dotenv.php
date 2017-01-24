<?php
/**
 * Helpers functions to work with vlucas/phpdotenv
 */

namespace Siler\Dotenv;

/**
 * Load the .env file contents into the environment
 *
 * @param string $path Dirname of the .env file location
 */
function init($path)
{
    $dotenv = new \Dotenv\Dotenv($path);
    return $dotenv->load();
}

/**
 * Get an environment value or fallback to the given default
 *
 * @param string $key The key to be searched on the environment
 * @param mixed $default A default when the key do not exists
 *
 * @return mixed
 */
function env($key = null, $default = null)
{
    return \Siler\array_get($_SERVER, $key, $default);
}
