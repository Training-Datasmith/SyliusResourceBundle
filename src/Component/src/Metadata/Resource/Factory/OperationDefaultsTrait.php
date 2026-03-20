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
namespace Sylius\Resource\Metadata\Resource\Factory;

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Symfony\Request\State\Responder;
use Sylius\Resource\Symfony\Routing\Factory\Route_Name\Operation_Route_Name_Factory_Interface;
/**
 * @internal
 */
trait Operation_Defaults_Trait
{
    private function get_resource_with_defaults(string $resource_class, Resource_Metadata $resource, Metadata_Interface $resource_configuration): Resource_Metadata
    {
        $resource = $resource->with_class($resource_class);
        if (null === $resource->get_alias()) {
            $resource = $resource->with_alias($resource_configuration->get_alias());
        }
        if (null === $resource->get_application_name()) {
            $resource = $resource->with_application_name($resource_configuration->get_application_name());
        }
        if (null === $resource->get_name()) {
            return $resource->with_name($resource_configuration->get_name());
        }
        return $resource;
    }
    private function get_operation_with_defaults(Operation $operation, Resource_Metadata $resource, Operation_Route_Name_Factory_Interface $operation_route_name_factory, Registry_Interface $resource_registry): array
    {
        $resource_configuration = $resource_registry->get($resource->get_alias() ?? '');
        $operation = $operation->with_resource($resource);
        if (null === $resource->get_name()) {
            $resource_name = $resource_configuration->get_name();
            $resource = $resource->with_name($resource_name);
            $operation = $operation->with_resource($resource);
        }
        if (null === $resource->get_plural_name()) {
            $resource_plural_name = $resource_configuration->get_plural_name();
            $resource = $resource->with_plural_name($resource_plural_name);
            $operation = $operation->with_resource($resource);
        }
        if (null === $operation->get_normalization_context()) {
            $operation = $operation->with_normalization_context($resource->get_normalization_context());
        }
        if (null === $operation->get_denormalization_context()) {
            $operation = $operation->with_denormalization_context($resource->get_denormalization_context());
        }
        if (null === $operation->get_validation_context()) {
            $operation = $operation->with_validation_context($resource->get_validation_context());
        }
        $operation = $operation->with_resource($resource);
        if (null === $operation->get_repository()) {
            $operation = $operation->with_repository($resource_configuration->get_service_id('repository'));
        }
        if (null === $operation->get_form_type()) {
            $form_type = $resource->get_form_type();
            $form_type ??= $resource_configuration->has_class('form') ? $resource_configuration->get_class('form') : null;
            if (null !== $form_type) {
                $operation = $operation->with_form_type($form_type);
            }
        }
        if (null !== $operation->get_form_type()) {
            $form_options = $this->build_form_options($operation, $resource_configuration);
            $operation = $operation->with_form_options($form_options);
        }
        if ($operation instanceof Http_Operation) {
            if (null === $operation->get_route_prefix()) {
                $operation = $operation->with_route_prefix($resource->get_route_prefix());
            }
            if (null === $operation->get_route_requirements()) {
                $operation = $operation->with_route_requirements($resource->get_route_requirements());
            }
            if (null === $operation->get_route_condition()) {
                $operation = $operation->with_route_condition($resource->get_route_condition());
            }
            if (null === $operation->get_route_priority()) {
                $operation = $operation->with_route_priority($resource->get_route_priority());
            }
            if (null === $operation->get_twig_context_factory()) {
                $operation = $operation->with_twig_context_factory('sylius.twig.context.factory.default');
            }
            if (null === $route_name = $operation->get_route_name()) {
                $route_name = $operation_route_name_factory->create_route_name($operation);
                $operation = $operation->with_route_name($route_name);
            }
            if (null === $operation->get_responder()) {
                $operation = $operation->with_responder(Responder::class);
            }
            $operation = $operation->with_name($route_name);
        }
        $operation_name = $operation->get_name();
        return [$operation_name, $operation];
    }
    private function build_form_options(Operation $operation, Metadata_Interface $resource_configuration): array
    {
        $form_options = array_merge(['data_class' => $resource_configuration->get_class('model')], $operation->get_form_options() ?? []);
        $validation_groups = $operation->get_validation_context()['groups'] ?? null;
        if (null !== $validation_groups) {
            return array_merge(['validation_groups' => $validation_groups], $form_options);
        }
        return $form_options;
    }
}