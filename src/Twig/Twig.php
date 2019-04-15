<?php

declare(strict_types=1);
/*
 * Helper functions to work with the Twig template engine.
 */

namespace Siler\Twig;

use RuntimeException;
use Siler\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Initialize the Twig environment.
 *
 * @param string $templatesPath Path to templates
 * @param string|false $templatesCachePath Path to templates cache
 * @param bool $debug Should TwigEnv allow debugging
 *
 * @return Environment
 */
function init(string $templatesPath, $templatesCachePath = false, bool $debug = false): Environment
{
    $twig = new Environment(
        /* @phan-suppress-next-line PhanDeprecatedInterface */
        new FilesystemLoader($templatesPath),
        [
            'debug' => $debug,
            'cache' => $templatesCachePath
        ]
    );

    Container\set('twig', $twig);

    return $twig;
}

/**
 * Renders the given template within the given data.
 *
 * @param string $name The template name in the templates path
 * @param array $data The array of data to used within the template
 *
 * @return string
 * @throws RuntimeException if Twig isn't initialized
 *
 */
function render(string $name, array $data = []): string
{
    $twig = Container\get('twig');

    if (is_null($twig)) {
        throw new RuntimeException('Twig should be initialized first');
    }

    return $twig->render($name, $data);
}
