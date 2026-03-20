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
namespace Sylius\Resource\Symfony\Session\Flash;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Symfony\Event_Dispatcher\Generic_Event;
/**
 * @experimental
 */
interface Flash_Helper_Interface
{
    public function add_success_flash(Operation $operation, Context $context, ?string $message = null): void;
    public function add_error_flash(Operation $operation, Context $context, ?string $message = null): void;
    public function add_flash_from_event(Generic_Event $event, Context $context): void;
}