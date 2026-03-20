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
namespace Sylius\Resource\State_Machine\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Processor_Interface;
use Sylius\Resource\State_Machine\Operation_State_Machine_Interface;
final readonly class Apply_State_Machine_Transition_Processor implements Processor_Interface
{
    public function __construct(private Operation_State_Machine_Interface $state_machine, private ?Processor_Interface $write_processor = null)
    {
    }
    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        if (\is_object($data) && $this->state_machine->can($data, $operation, $context)) {
            $this->state_machine->apply($data, $operation, $context);
        }
        return $this->write_processor?->process($data, $operation, $context);
    }
}