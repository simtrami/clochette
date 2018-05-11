<?php

/**
 * /src/DependencyInjection/EventsCompilerPass.php
 *
 * @copyright 2013 Sorin Badea <sorin.badea91@gmail.com>
 * @license   MIT license (see the license file in the root directory)
 */

namespace ThinFrame\Events\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * EventsCompilerPass - Symfony2 Dic Compiler Pass
 *
 * Adds each service tagged with thinframe.events.listener to a default events dispatcher
 * or to a custom one specified with "parent" attribute
 *
 * @package ThinFrame\Events\DependencyInjection
 * @since   0.2
 */
class EventsCompilerPass implements CompilerPassInterface
{
    const EVENT_DISPATCHER_TAG = 'thinframe.events.dispatcher';
    const EVENT_LISTENER_TAG   = 'thinframe.events.listener';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws LogicException
     */
    public function process(ContainerBuilder $container)
    {
        $dispatcher = $container->findTaggedServiceIds(self::EVENT_DISPATCHER_TAG);
        if (count($dispatcher) != 1) {
            throw new LogicException('One event dispatcher is mandatory');
        }
        $dispatcherId = key($dispatcher);
        foreach ($container->findTaggedServiceIds(self::EVENT_LISTENER_TAG) as $listenerId => $options) {
            $container->getDefinition($dispatcherId)->addMethodCall('attachListener', [new Reference($listenerId)]);
        }
    }
}
