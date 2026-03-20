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
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\Load_Class_Metadata_Event_Args;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Association_Mapping;
use Doctrine\ORM\Mapping\Class_Metadata;
use Doctrine\Persistence\Mapping\Driver\Mapping_Driver;
use Webmozart\Assert\Assert;
final class Orm_Mapped_Super_Class_Subscriber extends Abstract_Doctrine_Listener implements Event_Subscriber
{
    /**
     * @deprecated since version 1.10, It will be removed in 2.0.
     */
    public function get_subscribed_events(): array
    {
        return [Events::loadClassMetadata];
    }
    public function load_class_metadata(Load_Class_Metadata_Event_Args $event_args): void
    {
        $metadata = $event_args->get_class_metadata();
        if (!$metadata->is_mapped_superclass) {
            $this->set_association_mappings($metadata, $event_args->get_entity_manager()->get_configuration());
        } else {
            $this->unset_association_mappings($metadata);
        }
    }
    private function set_association_mappings(Class_Metadata $metadata, Configuration $configuration): void
    {
        $class = $metadata->get_name();
        if (!class_exists($class)) {
            return;
        }
        /** @psalm-suppress DeprecatedClass */
        $metadata_driver = $configuration->get_metadata_driver_impl();
        Assert::is_instance_of($metadata_driver, Mapping_Driver::class);
        $parents = class_parents($class) ?: [];
        foreach ($parents as $parent) {
            if (false === in_array($parent, $metadata_driver->get_all_class_names(), true)) {
                continue;
            }
            $parent_metadata = new Class_Metadata($parent, $configuration->get_naming_strategy());
            // Wakeup Reflection
            /** @psalm-suppress ArgumentTypeCoercion */
            $parent_metadata->wakeup_reflection($this->get_reflection_service());
            // Load Metadata
            $metadata_driver->load_metadata_for_class($parent, $parent_metadata);
            /** @psalm-suppress InvalidArgument */
            if (false === $this->is_resource($parent_metadata)) {
                continue;
            }
            if ($parent_metadata->is_mapped_superclass) {
                /**
                 * @var AssociationMapping|array{type: int} $value
                 */
                foreach ($parent_metadata->get_association_mappings() as $key => $value) {
                    $type = \is_array($value) ? $value['type'] : $value->type();
                    if ($this->is_relation($type) && !isset($metadata->association_mappings[$key])) {
                        if (\is_array($value)) {
                            $value['sourceEntity'] = $class;
                        } else {
                            /** @psalm-suppress UndefinedClass */
                            $value->source_entity = $class;
                            /** @phpstan-ignore-line */
                        }
                        $metadata->association_mappings[$key] = $value;
                        /** @phpstan-ignore-line */
                    }
                }
            }
        }
    }
    private function unset_association_mappings(Class_Metadata $metadata): void
    {
        /** @psalm-suppress InvalidArgument */
        if (false === $this->is_resource($metadata)) {
            return;
        }
        /**
         * @var AssociationMapping|array{type: int} $value
         */
        foreach ($metadata->get_association_mappings() as $key => $value) {
            $type = \is_array($value) ? $value['type'] : $value->type();
            if ($this->is_relation($type)) {
                unset($metadata->association_mappings[$key]);
            }
        }
    }
    private function is_relation(int $type): bool
    {
        return in_array($type, [Class_Metadata::MANY_TO_MANY, Class_Metadata::ONE_TO_MANY, Class_Metadata::ONE_TO_ONE], true);
    }
}