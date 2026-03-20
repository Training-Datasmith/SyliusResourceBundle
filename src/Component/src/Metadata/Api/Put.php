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
namespace Sylius\Resource\Metadata\Api;

use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Update_Operation_Interface;
/**
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Put extends Http_Operation implements Update_Operation_Interface, Api_Operation_Interface
{
    public function __construct(?string $path = null, ?string $route_name = null, ?string $route_prefix = null, ?array $route_requirements = null, ?string $route_condition = null, ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?string $repository_method = null, ?array $repository_arguments = null, ?bool $read = null, ?bool $write = null, ?bool $validate = null, ?bool $deserialize = null, ?bool $serialize = null, ?string $form_type = null, ?array $form_options = null, ?array $normalization_context = null, ?array $denormalization_context = null, ?array $validation_context = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, ?string $redirect_to_route = null)
    {
        parent::__construct(methods: ['PUT'], path: $path, routeName: $route_name, routePrefix: $route_prefix, routeRequirements: $route_requirements, routeCondition: $route_condition, routePriority: $route_priority, template: $template, shortName: $short_name ?? 'put', name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, validate: $validate, deserialize: $deserialize, serialize: $serialize, formType: $form_type, formOptions: $form_options, normalizationContext: $normalization_context, denormalizationContext: $denormalization_context, validationContext: $validation_context, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message, redirectToRoute: $redirect_to_route);
    }
}