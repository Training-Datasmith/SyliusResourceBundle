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
use Doctrine\ODM\Mongo_Db\Event\Lifecycle_Event_Args;
use Doctrine\ODM\Mongo_Db\Event\Load_Class_Metadata_Event_Args;
use Doctrine\ODM\Mongo_Db\Events;
use Doctrine\ODM\Mongo_Db\Mapping\Class_Metadata;
use Sylius\Resource\Model\Translatable_Interface;
use Sylius\Resource\Model\Translation_Interface;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Odm_Translatable_Listener::class);
final class Odm_Translatable_Listener implements Event_Subscriber
{
    /** @var string */
    private $current_locale;
    public function __construct(private array $mappings, private readonly string $fallback_locale)
    {
    }
    public function set_current_locale($current_locale): self
    {
        $this->current_locale = $current_locale;
        return $this;
    }
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
        $reflection = $class_metadata->refl_class;
        if (!$reflection || $reflection->is_abstract()) {
            return;
        }
        if ($reflection->implements_interface(Translatable_Interface::class)) {
            $this->map_translatable($class_metadata);
        }
        if ($reflection->implements_interface(Translation_Interface::class)) {
            $this->map_translation($class_metadata);
        }
    }
    /**
     * Add mapping data to a translatable entity
     */
    private function map_translatable(Class_Metadata $metadata): void
    {
        // In the case A -> B -> TranslatableInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return.
        if (!isset($this->mappings[$metadata->name])) {
            return;
        }
        $config = $this->mappings[$metadata->name];
        $mapping = $config['translation']['mapping'];
        $metadata->map_many_embedded(['fieldName' => $mapping['translatable']['translations'], 'targetDocument' => $config['translation']['model'], 'strategy' => 'set']);
    }
    /**
     * Add mapping data to a translation entity
     */
    private function map_translation(Class_Metadata $metadata): void
    {
        // In the case A -> B -> TranslationInterface, B might not have mapping defined as it
        // is probably defined in A, so in that case, we just return;
        if (!isset($this->mappings[$metadata->name])) {
            return;
        }
        $config = $this->mappings[$metadata->name];
        $mapping = $config['translation']['mapping'];
        $metadata->is_embedded_document = true;
        $metadata->is_mapped_superclass = false;
        $metadata->set_identifier(null);
        // Map locale field.
        if (!$metadata->has_field($mapping['translation']['locale'])) {
            $metadata->map_field(['fieldName' => $mapping['translation']['locale'], 'type' => 'string']);
        }
        // Map unique index.
        $keys = [$mapping['translation']['translatable'] => 1, $mapping['translation']['locale'] => 1];
        if (!$this->has_unique_index($metadata, $keys)) {
            $metadata->add_index($keys, ['unique' => true]);
        }
    }
    /**
     * Load translations
     */
    public function post_load(Lifecycle_Event_Args $args): void
    {
        $document = $args->get_document();
        // Sometimes $document is a doctrine proxy class, we therefore need to retrieve it's real class
        $name = $args->get_document_manager()->get_class_metadata($document::class)->get_name();
        if (!isset($this->mappings[$name])) {
            return;
        }
        $metadata = $this->mappings[$name];
        if (isset($metadata['fallback_locale'])) {
            $setter = 'set' . ucfirst($metadata['fallback_locale']);
            $document->{$setter}($this->fallback_locale);
        }
        if (isset($metadata['current_locale'])) {
            $setter = 'set' . ucfirst($metadata['current_locale']);
            $document->{$setter}($this->current_locale);
        }
    }
}