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
namespace Sylius\Resource\Symfony\Event_Dispatcher;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
/**
 * @experimental
 */
final class Operation_Event extends Generic_Event
{
    public function get_operation(): Operation
    {
        /** @var Operation $operation */
        $operation = $this->get_argument('operation');
        return $operation;
    }
    public function get_context(): Context
    {
        /** @var Context $context */
        $context = $this->get_argument('context');
        return $context;
    }
}