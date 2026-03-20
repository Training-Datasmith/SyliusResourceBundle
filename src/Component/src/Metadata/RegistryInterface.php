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
 * Interface for the registry of all resources.
 */
interface Registry_Interface
{
    /**
     * @return iterable|MetadataInterface[]
     */
    public function get_all(): iterable;
    /**
     * @throws \InvalidArgumentException
     */
    public function get(string $alias): Metadata_Interface;
    /**
     * @throws \InvalidArgumentException
     */
    public function get_by_class(string $class_name): Metadata_Interface;
    public function add(Metadata_Interface $metadata): void;
    public function add_from_alias_and_configuration(string $alias, array $configuration): void;
}
if (!class_exists(\Sylius\Component\Resource\Metadata\Registry_Interface::class, false)) {
    class_alias(Registry_Interface::class, \Sylius\Component\Resource\Metadata\Registry_Interface::class);
}