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
namespace Sylius\Resource\Metadata;

/**
 * The Operation has a state machine.
 *
 * @experimental
 */
interface State_Machine_Aware_Operation_Interface
{
    public function get_state_machine_component(): ?string;
    public function with_state_machine_component(?string $state_machine_component): self;
    public function get_state_machine_transition(): ?string;
    public function with_state_machine_transition(string $state_machine_transition): self;
    public function get_state_machine_graph(): ?string;
    public function with_state_machine_graph(string $state_machine_graph): self;
}