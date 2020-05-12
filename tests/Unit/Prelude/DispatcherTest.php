<?php declare(strict_types=1);

namespace Siler\Test\Unit\Prelude;

use PHPUnit\Framework\TestCase;
use Siler\Prelude\Dispatcher;
use Siler\Test\fixtures\TestEvent;

class DispatcherTest extends TestCase
{
    public function testListenAndDispatch()
    {
        $dispatcher = new Dispatcher();

        $dispatcher->listen(TestEvent::class, function (TestEvent $event) {
            $this->assertSame('test', $event->payload);
        });

        $dispatcher->dispatch(new TestEvent('test'));
    }

    public function testGetListenersForEvent()
    {
        $dispatcher = new Dispatcher();

        $callback = function (TestEvent $event) {
        };

        $dispatcher->listen(TestEvent::class, $callback);

        $this->assertSame([$callback], $dispatcher->getListenersForEvent(new TestEvent('test')));
    }
}
