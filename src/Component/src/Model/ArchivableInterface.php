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

interface Archivable_Interface
{
    public function get_archived_at(): ?\DateTimeInterface;
    public function set_archived_at(?\DateTimeInterface $archived_at): void;
}
if (!class_exists(\Sylius\Component\Resource\Model\Archivable_Interface::class, false)) {
    class_alias(Archivable_Interface::class, \Sylius\Component\Resource\Model\Archivable_Interface::class);
}