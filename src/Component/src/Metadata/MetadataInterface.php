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
 * @method string|null getStateMachineComponent()
 */
interface Metadata_Interface
{
    public function get_alias(): string;
    public function get_application_name(): string;
    public function get_name(): string;
    public function get_humanized_name(): string;
    public function get_plural_name(): string;
    public function get_driver(): string|false;
    public function get_templates_namespace(): ?string;
    /**
     * @return string|array
     *
     * @throws \InvalidArgumentException
     */
    public function get_parameter(string $name);
    /**
     * @return class-string $name
     *
     * @throws \InvalidArgumentException
     */
    public function get_class(string $name): string;
    /**
     * Return all the metadata parameters.
     */
    public function get_parameters(): array;
    public function has_parameter(string $name): bool;
    public function has_class(string $name): bool;
    public function get_service_id(string $service_name): string;
    public function get_permission_code(string $permission_name): string;
}
if (!class_exists(\Sylius\Component\Resource\Metadata\Metadata_Interface::class, false)) {
    class_alias(Metadata_Interface::class, \Sylius\Component\Resource\Metadata\Metadata_Interface::class);
}