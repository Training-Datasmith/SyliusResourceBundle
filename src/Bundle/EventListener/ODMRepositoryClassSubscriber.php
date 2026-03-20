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

use Doctrine\ODM\Mongo_Db\Event\Load_Class_Metadata_Event_Args;
use Doctrine\ODM\Mongo_Db\Events;
use Doctrine\ODM\Mongo_Db\Mapping\Class_Metadata;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Odm_Repository_Class_Subscriber::class);
final class Odm_Repository_Class_Subscriber extends Abstract_Doctrine_Subscriber
{
    public function get_subscribed_events(): array
    {
        return [Events::loadClassMetadata];
    }
    public function load_class_metadata(Load_Class_Metadata_Event_Args $event_args): void
    {
        $this->set_custom_repository_class($event_args->get_class_metadata());
    }
    private function set_custom_repository_class(Class_Metadata $metadata): void
    {
        try {
            $resource_metadata = $this->resource_registry->get_by_class($metadata->get_name());
        } catch (\InvalidArgumentException) {
            return;
        }
        if ($resource_metadata->has_class('repository')) {
            $metadata->set_custom_repository_class($resource_metadata->get_class('repository'));
        }
    }
}