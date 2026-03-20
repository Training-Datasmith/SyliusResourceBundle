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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Event_Listener;

use Doctrine\ODM\PHPCR\Document_Manager_Interface;
use Doctrine\ODM\PHPCR\Mapping\Class_Metadata;
use PHPCR\Util\Node_Helper;
use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Default_Parent_Listener::class);
/**
 * Automatically set the parent brefore the creation.
 */
class Default_Parent_Listener
{
    /** @var DocumentManagerInterface */
    private $document_manager;
    public function __construct(Document_Manager_Interface $document_manager, private readonly string $parent_path, private readonly bool $autocreate = false, private readonly bool $force = false)
    {
        $this->document_manager = $document_manager;
    }
    public function on_pre_create(Resource_Controller_Event $event): void
    {
        $document = $event->get_subject();
        $class = $document::class;
        $this->resolve_parent($document, $this->document_manager->get_class_metadata($class));
    }
    private function resolve_parent($document, Class_Metadata $metadata): void
    {
        if (!$parent_field = $metadata->parent_mapping) {
            throw new \RuntimeException(sprintf('A default parent path has been specified, but no parent mapping has been applied to document "%s"', $document::class));
        }
        if (false === $this->force) {
            $actual_parent = $metadata->get_field_value($document, $parent_field);
            if ($actual_parent) {
                return;
            }
        }
        $parent_document = $this->document_manager->find(null, $this->parent_path);
        if (true === $this->autocreate && null === $parent_document) {
            Node_Helper::create_path($this->document_manager->get_phpcr_session(), $this->parent_path);
            $parent_document = $this->document_manager->find(null, $this->parent_path);
        }
        if (null === $parent_document) {
            throw new \RuntimeException(sprintf('Document at default parent path "%s" does not exist. `autocreate` was set to "%s"', $this->parent_path, $this->autocreate ? 'true' : 'false'));
        }
        $metadata->set_field_value($document, $parent_field, $parent_document);
    }
}