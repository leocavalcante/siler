<?php declare(strict_types=1);

namespace Siler\Prelude;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class Dispatcher
 * @package Siler\Event
 */
final class Dispatcher implements EventDispatcherInterface, ListenerProviderInterface
{
    /**
     * @var array<class-string, callable[]>
     */
    private $listeners = [];

    /**
     * Listen for an event using its class name as pattern.
     *
     * @template E
     * @param string $eventClass
     * @psalm-param class-string $eventClass
     * @param callable(E):void $callback
     * @return $this
     */
    public function listen(string $eventClass, callable $callback): self
    {
        if (empty($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }

        $this->listeners[$eventClass][] = $callback;
        return $this;
    }

    /**
     * Dispatch the given event.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        $event_class = get_class($event);

        foreach ($this->listeners[$event_class] as $callback) {
            $callback($event);
        }

        return $event;
    }

    /**
     * Returns the listeners for the given event.
     *
     * @param object $event
     * @return iterable
     */
    public function getListenersForEvent(object $event): iterable
    {
        $event_class = get_class($event);
        return $this->listeners[$event_class];
    }
}
