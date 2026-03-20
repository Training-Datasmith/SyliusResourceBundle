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
namespace Sylius\Resource\Metadata;

/**
 * @experimental
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class Bulk_Delete extends Http_Operation implements Delete_Operation_Interface, Bulk_Operation_Interface
{
    public function __construct(?array $methods = null, ?string $path = null, ?string $route_name = null, ?string $route_prefix = null, ?array $route_requirements = null, ?string $route_condition = null, ?int $route_priority = null, ?string $template = null, ?string $short_name = null, ?string $name = null, string|callable|null $provider = null, string|callable|null $processor = null, string|callable|null $responder = null, string|callable|null $repository = null, ?array $repository_arguments = null, ?string $repository_method = null, ?bool $read = null, ?bool $write = null, ?string $form_type = null, ?array $form_options = null, ?string $event_short_name = null, ?string $notification_message = null, string|\Stringable|null $security = null, ?string $security_message = null, ?string $redirect_to = null, ?string $redirect_to_route = null, ?array $redirect_arguments = null, ?array $vars = null)
    {
        parent::__construct(methods: $methods ?? ['DELETE', 'POST'], path: $path, routeName: $route_name, routePrefix: $route_prefix, routeRequirements: $route_requirements, routeCondition: $route_condition, routePriority: $route_priority, template: $template, shortName: $short_name ?? 'bulk_delete', name: $name, provider: $provider, processor: $processor, responder: $responder, repository: $repository, repositoryMethod: $repository_method, repositoryArguments: $repository_arguments, read: $read, write: $write, formType: $form_type, formOptions: $form_options, eventShortName: $event_short_name, notificationMessage: $notification_message, security: $security, securityMessage: $security_message, redirectTo: $redirect_to, redirectToRoute: $redirect_to_route, redirectArguments: $redirect_arguments, vars: $vars);
    }
}