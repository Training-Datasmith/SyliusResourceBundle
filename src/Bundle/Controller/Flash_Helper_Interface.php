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

use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
use Sylius\Resource\Model\Resource_Interface;
interface Flash_Helper_Interface
{
    public function add_success_flash(Request_Configuration $request_configuration, string $action_name, ?Resource_Interface $resource = null): void;
    public function add_error_flash(Request_Configuration $request_configuration, string $action_name): void;
    public function add_flash_from_event(Request_Configuration $request_configuration, Resource_Controller_Event $event): void;
}