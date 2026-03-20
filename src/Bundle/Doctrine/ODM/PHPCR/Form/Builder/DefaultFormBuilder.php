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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Form\Builder;

use Doctrine\ODM\PHPCR\Document_Manager_Interface;
use Sylius\Bundle\Resource_Bundle\Form\Builder\Default_Form_Builder_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Default_Form_Builder::class);
class Default_Form_Builder implements Default_Form_Builder_Interface
{
    /** @var DocumentManagerInterface */
    private $document_manager;
    public function __construct(Document_Manager_Interface $document_manager)
    {
        $this->document_manager = $document_manager;
    }
    public function build(Metadata_Interface $metadata, Form_Builder_Interface $form_builder, array $options): void
    {
        $class_metadata = $this->document_manager->get_class_metadata($metadata->get_class('model'));
        // the field mappings should only contain standard value mappings
        foreach ($class_metadata->field_mappings as $field_name) {
            if ($field_name === $class_metadata->uuid_field_name) {
                continue;
            }
            if ($field_name === $class_metadata->nodename) {
                continue;
            }
            $options = [];
            $mapping = $class_metadata->mappings[$field_name];
            if ($mapping['nullable'] === false) {
                $options['required'] = true;
            }
            $form_builder->add($field_name, null, $options);
        }
    }
}