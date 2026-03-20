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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ORM\Form\Builder;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Entity_Manager_Interface;
use Doctrine\ORM\Mapping\Class_Metadata;
use Sylius\Bundle\Resource_Bundle\Form\Builder\Default_Form_Builder_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Form\Form_Builder_Interface;
use Webmozart\Assert\Assert;
class Default_Form_Builder implements Default_Form_Builder_Interface
{
    private readonly Entity_Manager_Interface $entity_manager;
    public function __construct(Entity_Manager_Interface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }
    public function build(Metadata_Interface $metadata, Form_Builder_Interface $form_builder, array $options): void
    {
        $class_metadata = $this->entity_manager->get_class_metadata($metadata->get_class('model'));
        if (1 < count($class_metadata->identifier)) {
            throw new \RuntimeException('The default form factory does not support entity classes with multiple primary keys.');
        }
        $this->do_build($class_metadata, $form_builder);
    }
    private function do_build(Class_Metadata $class_metadata, Form_Builder_Interface $form_builder): void
    {
        $fields = $class_metadata->field_names;
        if (!$class_metadata->is_identifier_natural()) {
            $fields = array_diff($fields, $class_metadata->identifier);
        }
        foreach ($fields as $field_name) {
            $options = [];
            // Skip fields coming from embeddables
            if (str_contains($field_name, '.')) {
                continue;
            }
            if (in_array($field_name, ['createdAt', 'updatedAt'], true)) {
                continue;
            }
            if (Types::DATETIME_MUTABLE === $class_metadata->get_type_of_field($field_name)) {
                $options = ['widget' => 'single_text'];
            }
            $form_builder->add($field_name, null, $options);
        }
        foreach ($class_metadata->embedded_classes as $field_name => $embedded_mapping) {
            $nested_form_builder = $form_builder->create($field_name, null, ['data_class' => $embedded_mapping['class'], 'compound' => true]);
            Assert::string_not_empty($embedded_mapping['class']);
            $this->do_build($this->entity_manager->get_class_metadata($embedded_mapping['class']), $nested_form_builder);
            $form_builder->add($nested_form_builder);
        }
        foreach ($class_metadata->get_association_mappings() as $field_name => $association_mapping) {
            if (Class_Metadata::ONE_TO_MANY !== $association_mapping['type']) {
                $form_builder->add($field_name, null, ['choice_label' => 'id']);
            }
        }
    }
}