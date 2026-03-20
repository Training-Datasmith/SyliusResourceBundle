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
namespace Sylius\Bundle\Resource_Bundle\Event_Listener;

use Doctrine\Common\Event_Subscriber;
trigger_deprecation('sylius/resource-bundle', '1.10', 'The "%s" class is deprecated, use %s instead. It will be removed in 2.0.', Abstract_Doctrine_Subscriber::class, Abstract_Doctrine_Listener::class);
abstract class Abstract_Doctrine_Subscriber extends Abstract_Doctrine_Listener implements Event_Subscriber
{
}