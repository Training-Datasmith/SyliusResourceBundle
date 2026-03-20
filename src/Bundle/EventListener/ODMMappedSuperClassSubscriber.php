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
use Doctrine\ODM\Mongo_Db\Mapping\Class_Metadata_Info;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Odm_Mapped_Super_Class_Subscriber::class);
/**
 * Doctrine listener used to manipulate mappings.
 */
final class Odm_Mapped_Super_Class_Subscriber extends Abstract_Doctrine_Subscriber
{
    public function get_subscribed_events(): array
    {
        return [Events::loadClassMetadata];
    }
    public function load_class_metadata(Load_Class_Metadata_Event_Args $event_args): void
    {
        $metadata = $event_args->get_class_metadata();
        $this->convert_to_document_if_needed($metadata);
        if (!$metadata->is_mapped_superclass) {
            $this->set_association_mappings($metadata, $event_args->get_document_manager()->get_configuration());
        } else {
            $this->unset_association_mappings($metadata);
        }
    }
    private function convert_to_document_if_needed(Class_Metadata_Info $metadata): void
    {
        if (false === $metadata->is_mapped_superclass) {
            return;
        }
        try {
            $resource_metadata = $this->resource_registry->get_by_class($metadata->get_name());
        } catch (\InvalidArgumentException) {
            return;
        }
        if ($metadata->get_name() === $resource_metadata->get_class('model')) {
            $metadata->is_mapped_superclass = false;
        }
    }
    /**
     * @param $configuration
     */
    private function set_association_mappings(Class_Metadata_Info $metadata, $configuration): void
    {
        foreach (class_parents($metadata->get_name()) as $parent) {
            if (false === in_array($parent, $configuration->get_metadata_driver_impl()->get_all_class_names())) {
                continue;
            }
            $parent_metadata = new Class_Metadata($parent, $configuration->get_naming_strategy());
            // Wakeup Reflection
            $parent_metadata->wakeup_reflection($this->get_reflection_service());
            // Load Metadata
            $configuration->get_metadata_driver_impl()->load_metadata_for_class($parent, $parent_metadata);
            if (false === $this->is_resource($parent_metadata)) {
                continue;
            }
            if ($parent_metadata->is_mapped_superclass) {
                foreach ($parent_metadata->association_mappings as $key => $value) {
                    if ($this->is_relation($value['association']) && !isset($metadata->association_mappings[$key])) {
                        $metadata->association_mappings[$key] = $value;
                    }
                }
            }
        }
    }
    private function unset_association_mappings(Class_Metadata_Info $metadata): void
    {
        if (false === $this->is_resource($metadata)) {
            return;
        }
        foreach ($metadata->association_mappings as $key => $value) {
            if ($this->is_relation($value['association'])) {
                unset($metadata->association_mappings[$key]);
            }
        }
    }
    /**
     * @param string $type
     */
    private function is_relation($type): bool
    {
        return in_array($type, [Class_Metadata_Info::REFERENCE_ONE, Class_Metadata_Info::REFERENCE_MANY, Class_Metadata_Info::EMBED_ONE, Class_Metadata_Info::EMBED_MANY], true);
    }
}