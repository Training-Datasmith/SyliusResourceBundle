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

/**
 * @see ArchivableInterface
 */
trait Archivable_Trait
{
    /** @var \DateTimeInterface|null */
    protected $archived_at;
    public function get_archived_at(): ?\DateTimeInterface
    {
        return $this->archived_at;
    }
    public function set_archived_at(?\DateTimeInterface $archived_at): void
    {
        $this->archived_at = $archived_at;
    }
}
if (!class_exists(\Sylius\Component\Resource\Model\Archivable_Trait::class, false)) {
    class_alias(Archivable_Trait::class, \Sylius\Component\Resource\Model\Archivable_Trait::class);
}