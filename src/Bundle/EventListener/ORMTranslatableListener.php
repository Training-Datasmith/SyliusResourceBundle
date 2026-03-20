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
use Doctrine\ORM\Event\Load_Class_Metadata_Event_Args;
use Doctrine\ORM\Event\Post_Load_Event_Args;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Class_Metadata;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Model\Translatable_Interface;
use Sylius\Resource\Model\Translation_Interface;
use Sylius\Resource\Translation\Translatable_Entity_Locale_Assigner_Interface;
use Symfony\Component\Dependency_Injection\Container_Interface;
final readonly class Orm_Translatable_Listener implements Event_Subscriber
{
    private Translatable_Entity_Locale_Assigner_Interface $translatable_entity_locale_assigner;
    public function __construct(private Registry_Interface $resource_metadata_registry, object $translatable_entity_locale_assigner)
    {
        $this->translatable_entity_locale_assigner = $this->process_translatable_entity_locale_assigner($translatable_entity_locale_assigner);
    }
    /**
     * @deprecated since version 1.10, It will be removed in 2.0.
     */
    public function get_subscribed_events(): array
    {
        return [Events::loadClassMetadata, Events::postLoad];
    }
    /**
     * Add mapping to translatable entities
     */
    public function load_class_metadata(Load_Class_Metadata_Event_Args $event_args): void
    {
        $class_metadata = $event_args->get_class_metadata();
        $reflection = $class_metadata->get_reflection_class();
        /** @psalm-suppress PossiblyNullReference */
        if ($reflection->is_abstract()) {
            return;
        }
        if ($reflection->implements_interface(Translatable_Interface::class)) {
            $this->map_translatable($class_metadata);
        }
        if ($reflection->implements_interface(Translation_Interface::class)) {
            $this->map_translation($class_metadata);
        }
    }
    public function post_load(Post_Load_Event_Args $args): void
    {
        $entity = $args->get_object();
        if (!$entity instanceof Translatable_Interface) {
            return;
        }
        $this->translatable_entity_locale_assigner->assign_locale($entity);
    }
    /**
     * Add mapping data to a translatable entity.
     */
    private function map_translatable(Class_Metadata $metadata): void
    {
        $class_name = $metadata->name;
        try {
            $resource_metadata = $this->resource_metadata_registry->get_by_class($class_name);
        } catch (\InvalidArgumentException) {
            return;
        }
        if (!$resource_metadata->has_parameter('translation')) {
            return;
        }
        /** @var MetadataInterface $translationResourceMetadata */
        $translation_resource_metadata = $this->resource_metadata_registry->get($resource_metadata->get_alias() . '_translation');
        if (!$metadata->has_association('translations')) {
            $metadata->map_one_to_many(['fieldName' => 'translations', 'targetEntity' => $translation_resource_metadata->get_class('model'), 'mappedBy' => 'translatable', 'fetch' => Class_Metadata::FETCH_EXTRA_LAZY, 'indexBy' => 'locale', 'cascade' => ['persist', 'remove'], 'orphanRemoval' => true]);
        }
    }
    /**
     * Add mapping data to a translation entity.
     */
    private function map_translation(Class_Metadata $metadata): void
    {
        $class_name = $metadata->name;
        try {
            $resource_metadata = $this->resource_metadata_registry->get_by_class($class_name);
        } catch (\InvalidArgumentException) {
            return;
        }
        /** @var MetadataInterface $translatableResourceMetadata */
        $translatable_resource_metadata = $this->resource_metadata_registry->get(str_replace('_translation', '', $resource_metadata->get_alias()));
        if (!$metadata->has_association('translatable')) {
            $metadata->map_many_to_one(['fieldName' => 'translatable', 'targetEntity' => $translatable_resource_metadata->get_class('model'), 'inversedBy' => 'translations', 'joinColumns' => [['name' => 'translatable_id', 'referencedColumnName' => 'id', 'onDelete' => 'CASCADE', 'nullable' => false]]]);
        }
        if (!$metadata->has_field('locale')) {
            $metadata->map_field(['fieldName' => 'locale', 'type' => 'string', 'nullable' => false]);
        }
        // Map unique index.
        $columns = [$metadata->get_single_association_join_column_name('translatable'), 'locale'];
        if (!$this->has_unique_constraint($metadata, $columns)) {
            $constraints = $metadata->table['uniqueConstraints'] ?? [];
            $constraints[$metadata->get_table_name() . '_uniq_trans'] = ['columns' => $columns];
            $metadata->set_primary_table(['uniqueConstraints' => $constraints]);
        }
    }
    /**
     * Check if a unique constraint has been defined.
     */
    private function has_unique_constraint(Class_Metadata $metadata, array $columns): bool
    {
        if (!isset($metadata->table['uniqueConstraints'])) {
            return false;
        }
        foreach ($metadata->table['uniqueConstraints'] as $constraint) {
            if (!array_diff($constraint['columns'], $columns)) {
                return true;
            }
        }
        return false;
    }
    private function process_translatable_entity_locale_assigner(object $translatable_entity_locale_assigner): Translatable_Entity_Locale_Assigner_Interface
    {
        if ($translatable_entity_locale_assigner instanceof Container_Interface) {
            trigger_deprecation('sylius/resource-bundle', '1.4', 'Passing an instance of "%s" is deprecated. Use "%s" instead.', Container_Interface::class, Translatable_Entity_Locale_Assigner_Interface::class);
            /** @var object $translatableEntityLocaleAssigner */
            $translatable_entity_locale_assigner = $translatable_entity_locale_assigner->get('sylius.translatable_entity_locale_assigner');
        }
        if (!$translatable_entity_locale_assigner instanceof Translatable_Entity_Locale_Assigner_Interface) {
            throw new \InvalidArgumentException(sprintf('`$translatableEntityLocaleAssigner` was expected to return an instance of "%s" , "%s" found', Translatable_Entity_Locale_Assigner_Interface::class, $translatable_entity_locale_assigner::class));
        }
        return $translatable_entity_locale_assigner;
    }
}