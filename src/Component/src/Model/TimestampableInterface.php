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
namespace Sylius\Resource\Model;

interface Timestampable_Interface
{
    public function get_created_at(): ?\DateTimeInterface;
    /** @psalm-suppress MissingReturnType */
    public function set_created_at(?\DateTimeInterface $created_at);
    public function get_updated_at(): ?\DateTimeInterface;
    /** @psalm-suppress MissingReturnType */
    public function set_updated_at(?\DateTimeInterface $updated_at);
}
if (!class_exists(\Sylius\Component\Resource\Model\Timestampable_Interface::class, false)) {
    class_alias(Timestampable_Interface::class, \Sylius\Component\Resource\Model\Timestampable_Interface::class);
}