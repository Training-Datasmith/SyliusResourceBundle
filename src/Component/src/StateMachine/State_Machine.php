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
namespace Sylius\Resource\State_Machine;

use SM\State_Machine\State_Machine as BaseStateMachine;
use Sylius\Resource\Exception\RuntimeException;
if (!class_exists(Base_State_Machine::class)) {
    throw new RuntimeException(sprintf('Cannot use the "%s" class when the "winzou/state-machine" package is not installed.', State_Machine::class));
}
final class State_Machine extends Base_State_Machine implements State_Machine_Interface
{
    public function get_transition_from_state(string $from_state): ?string
    {
        foreach ($this->get_possible_transitions() as $transition) {
            $config = $this->config['transitions'][$transition];
            if (in_array($from_state, $config['from'], true)) {
                return $transition;
            }
        }
        return null;
    }
    public function get_transition_to_state(string $to_state): ?string
    {
        foreach ($this->get_possible_transitions() as $transition) {
            $config = $this->config['transitions'][$transition];
            if ($to_state === $config['to']) {
                return $transition;
            }
        }
        return null;
    }
}
if (!class_exists(\Sylius\Component\Resource\State_Machine\State_Machine::class, false)) {
    class_alias(State_Machine::class, \Sylius\Component\Resource\State_Machine\State_Machine::class);
}