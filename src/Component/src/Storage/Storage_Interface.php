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
namespace Sylius\Resource\Storage;

use Sylius\Resource\Exception\Storage_Unavailable_Exception;
interface Storage_Interface
{
    /**
     * @throws StorageUnavailableException
     */
    public function has(string $name): bool;
    /**
     * @param mixed $default
     *
     * @return mixed
     *
     * @throws StorageUnavailableException
     */
    public function get(string $name, $default = null);
    /**
     * @param mixed $value
     *
     * @throws StorageUnavailableException
     */
    public function set(string $name, $value): void;
    /**
     * @throws StorageUnavailableException
     */
    public function remove(string $name): void;
    /**
     * @throws StorageUnavailableException
     */
    public function all(): array;
}
if (!class_exists(\Sylius\Component\Resource\Storage\Storage_Interface::class, false)) {
    class_alias(Storage_Interface::class, \Sylius\Component\Resource\Storage\Storage_Interface::class);
}