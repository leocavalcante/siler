<?php
/**
 * Helper functions to work with the Twig template engine.
 */

namespace Siler\Twig;

use Siler\Container;

/**
 * Initialize the Twig environment.
 *
 * @param string       $templatesPath      Path to templates
 * @param string|false $templatesCachePath Path to templates cache
 * @param bool         $debug              Should TwigEnv allow debugging
 *
 * @return \Twig_Environment
 */
function init($templatesPath, $templatesCachePath = false, $debug = false)
{
    $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($templatesPath), [
        'debug' => $debug,
        'cache' => $templatesCachePath,
    ]);

    Container\set('twig', $twig);

    return $twig;
}

/**
 * Renders the given template within the given data.
 *
 * @param string $name The template name in the templates path
 * @param array  $data The array of data to used within the template
 *
 * @throws \RuntimeException if Twig isn't initialized
 *
 * @return string
 */
function render($name, $data = [])
{
    $twig = Container\get('twig');

    if (is_null($twig)) {
        throw new \RuntimeException('Twig should be initialized first');
    }

    return $twig->render($name, $data);
}
