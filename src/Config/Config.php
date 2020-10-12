<?php declare(strict_types=1);

namespace Siler\Config;

use Laminas\Config\Config;
use Laminas\Config\Factory;
use Laminas\Config\Processor\ProcessorInterface;
use Laminas\Config\Processor\Queue;
use Laminas\Config\Reader\ReaderInterface;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\LaminasConfigProvider;
use Siler\Container;

const CONFIG = 'siler_config';
const CONFIG_PROCESSORS = 'siler_config_processors';

/**
 * Registers custom readers.
 *
 * @param array<string, ReaderInterface> $readers
 */
function readers(array $readers): void
{
    foreach ($readers as $extension => $reader) {
        Factory::registerReader($extension, $reader);
    }
}

/**
 * Registers configuration processors.
 *
 * @param array<ProcessorInterface> $processors
 */
function processors(array $processors): void
{
    Container\set(CONFIG_PROCESSORS, $processors);
}

/**
 * Gets a configuration.
 *
 * @param string $path
 * @param mixed $default
 * @return mixed
 */
function config(string $path, $default = null)
{
    $keys = explode('.', $path);

    /** @var array|null */
    $pointer = all();
    foreach ($keys as $key) {
        if (empty($pointer)) {
            return $default;
        }
        /** @var mixed */
        $pointer = $pointer[$key] ?? null;
    }

    return $pointer ?? $default;
}

/**
 * Initializes configuration object.
 *
 * @param string $directory
 * @return Config
 */
function load(string $directory): Config
{
    $aggregator = new ConfigAggregator([
        new LaminasConfigProvider($directory . '/*.*')
    ]);
    /** @var array */
    $data = $aggregator->getMergedConfig();
    $config = new Config($data, true);

    /** @var array<ProcessorInterface> */
    $processors = Container\get(CONFIG_PROCESSORS, []);
    $queue = new Queue();
    foreach ($processors as $processor) {
        $queue->insert($processor);
    }
    $queue->process($config);
    Container\set(CONFIG, $config);

    return $config;
}

/**
 * Checks if the key exists on the config.
 *
 * @param string $path
 * @return bool
 */
function has(string $path): bool
{
    return config($path) !== null;
}

/**
 * Returns all the configurations.
 *
 * @return array|null
 */
function all(): ?array
{
    /** @var Config|null $config */
    $config = Container\get(CONFIG);
    if ($config === null) {
        return null;
    }

    return $config->toArray();
}
