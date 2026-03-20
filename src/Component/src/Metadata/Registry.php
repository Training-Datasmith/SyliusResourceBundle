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

final class Registry implements Registry_Interface
{
    /** @var array|MetadataInterface[] */
    private array $metadata = [];
    public function get_all(): iterable
    {
        return $this->metadata;
    }
    public function get(string $alias): Metadata_Interface
    {
        if (!array_key_exists($alias, $this->metadata)) {
            throw new \InvalidArgumentException(sprintf('Resource "%s" does not exist.', $alias));
        }
        return $this->metadata[$alias];
    }
    public function get_by_class(string $class_name): Metadata_Interface
    {
        foreach ($this->metadata as $metadata) {
            if ($class_name === $metadata->get_class('model')) {
                return $metadata;
            }
        }
        throw new \InvalidArgumentException(sprintf('Resource with model class "%s" does not exist.', $class_name));
    }
    public function add(Metadata_Interface $metadata): void
    {
        $this->metadata[$metadata->get_alias()] = $metadata;
    }
    public function add_from_alias_and_configuration(string $alias, array $configuration): void
    {
        $this->add(Metadata::from_alias_and_configuration($alias, $configuration));
    }
}
if (!class_exists(\Sylius\Component\Resource\Metadata\Registry::class, false)) {
    class_alias(Registry::class, \Sylius\Component\Resource\Metadata\Registry::class);
}