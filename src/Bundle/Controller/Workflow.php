<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Sylius\Resource\Exception\RuntimeException;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Workflow\Registry;
use Webmozart\Assert\Assert;
if (!class_exists(Registry::class)) {
    throw new RuntimeException(sprintf('Cannot use the "%s" class when the "symfony/workflow" package is not installed.', Workflow::class));
}
final class Workflow implements State_Machine_Interface
{
    /** @var Registry */
    private $registry;
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }
    /**
     * @inheritdoc
     */
    public function can(Request_Configuration $configuration, Resource_Interface $resource): bool
    {
        Assert::true($configuration->has_state_machine(), 'State machine must be configured to apply transition, check your routing.');
        $graph = $configuration->get_state_machine_graph();
        /** @var string $transitionName */
        $transition_name = $configuration->get_state_machine_transition();
        return $this->registry->get($resource, $graph)->can($resource, $transition_name);
    }
    /**
     * @inheritdoc
     */
    public function apply(Request_Configuration $configuration, Resource_Interface $resource): void
    {
        Assert::true($configuration->has_state_machine(), 'State machine must be configured to apply transition, check your routing.');
        $graph = $configuration->get_state_machine_graph();
        /** @var string $transitionName */
        $transition_name = $configuration->get_state_machine_transition();
        $this->registry->get($resource, $graph)->apply($resource, $transition_name);
    }
}