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
namespace Sylius\Resource\Context;

/**
 * @implements \IteratorAggregate<object>
 */
final class Context implements \IteratorAggregate
{
    /** @var array<class-string, object> */
    private array $option_map;
    public function __construct(object ...$options)
    {
        $map = [];
        foreach ($options as $option) {
            $map[$option::class] = $option;
        }
        $this->option_map = $map;
    }
    public function with(object ...$options): self
    {
        /** @psalm-suppress DuplicateArrayKey */
        return new self(...[...array_values($this->option_map), ...$options]);
    }
    /**
     * @param class-string $optionClasses
     */
    public function without(string ...$option_classes): self
    {
        $option_map = $this->option_map;
        foreach ($option_classes as $option_class) {
            unset($option_map[$option_class]);
        }
        return new self(...array_values($option_map));
    }
    /**
     * @template T of object
     *
     * @param class-string<T> $optionClass
     *
     * @return T|null
     */
    public function get(string $option_class): ?object
    {
        /** @var T $option */
        $option = $this->option_map[$option_class] ?? null;
        return $option;
    }
    /**
     * @return \Traversable<object>
     */
    public function getIterator(): \Traversable
    {
        yield from array_values($this->option_map);
    }
}