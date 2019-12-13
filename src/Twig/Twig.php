<?php declare(strict_types=1);
/*
 * Helper functions to work with the Twig template engine.
 */

namespace Siler\Twig;

use Siler\Container;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use UnexpectedValueException;

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
 *
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function render(string $name, array $data = []): string
{
    /** @var Environment|null $twig */
    $twig = Container\get('twig');

    if ($twig === null) {
        throw new UnexpectedValueException('Twig should be initialized first');
    }

    return $twig->render($name, $data);
}
