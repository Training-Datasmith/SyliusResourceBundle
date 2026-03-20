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

trait Timestampable_Trait
{
    /** @var \DateTimeInterface|null */
    protected $created_at;
    /** @var \DateTimeInterface|null */
    protected $updated_at;
    public function get_created_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }
    public function set_created_at(?\DateTimeInterface $created_at): void
    {
        $this->created_at = $created_at;
    }
    public function get_updated_at(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }
    public function set_updated_at(?\DateTimeInterface $updated_at): void
    {
        $this->updated_at = $updated_at;
    }
}
if (!class_exists(\Sylius\Component\Resource\Model\Timestampable_Trait::class, false)) {
    class_alias(Timestampable_Trait::class, \Sylius\Component\Resource\Model\Timestampable_Trait::class);
}