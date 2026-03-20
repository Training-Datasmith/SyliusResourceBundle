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
use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Name_Resolver_Listener::class);
/**
 * Handles the resolution of the PHPCR node name field.
 *
 * If a node already exists with the same name, then a numerical index will be
 * appended to the name.
 */
class Name_Resolver_Listener
{
    /** @var DocumentManagerInterface */
    private $document_manager;
    public function __construct(Document_Manager_Interface $document_manager)
    {
        $this->document_manager = $document_manager;
    }
    public function on_event(Resource_Controller_Event $event): void
    {
        $document = $event->get_subject();
        $metadata = $this->document_manager->get_class_metadata($document::class);
        if ($metadata->id_generator !== Class_Metadata::GENERATOR_TYPE_PARENT) {
            throw new \RuntimeException(sprintf('Document of class "%s" must be using the GENERATOR_TYPE_PARENT identificatio strategy (value %s), it is current using "%s" (this may be an automatic configuration: be sure to map both the `nodename` and the `parentDocument`).', $document::class, Class_Metadata::GENERATOR_TYPE_PARENT, $metadata->id_generator));
        }
        // NOTE: that the PHPCR-ODM requires these two fields to be set when
        //       when the GENERATOR_TYPE_PARENT "ID" strategy is used.
        $name_field = $metadata->nodename;
        $parent_field = $metadata->parent_mapping;
        $parent_document = $metadata->get_field_value($document, $parent_field);
        $phpcr_node = $this->document_manager->get_node_for_document($parent_document);
        $parent_path = $phpcr_node->get_path();
        $base_candidate_name = $metadata->get_field_value($document, $name_field);
        $candidate_name = $base_candidate_name;
        $index = 1;
        while (true) {
            $candidate_path = sprintf('%s/%s', $parent_path, $candidate_name);
            $existing = $this->document_manager->find(null, $candidate_path);
            // if the existing document is the document we are updating, then thats great.
            if ($existing === $document) {
                return;
            }
            if (null === $existing) {
                $metadata->set_field_value($document, $name_field, $candidate_name);
                return;
            }
            $candidate_name = sprintf('%s-%d', $base_candidate_name, $index);
            ++$index;
        }
    }
}