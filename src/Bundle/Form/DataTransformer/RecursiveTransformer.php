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
namespace Sylius\Bundle\Resource_Bundle\Form\Data_Transformer;

use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Readable_Collection;
use Symfony\Component\Form\Data_Transformer_Interface;
use Symfony\Component\Form\Exception\Transformation_Failed_Exception;
final readonly class Recursive_Transformer implements Data_Transformer_Interface
{
    private Data_Transformer_Interface $decorated_transformer;
    public function __construct(Data_Transformer_Interface $decorated_transformer)
    {
        $this->decorated_transformer = $decorated_transformer;
    }
    /** @param Collection|null $value */
    public function transform($value): Readable_Collection
    {
        if (null === $value) {
            return new Array_Collection();
        }
        $this->assert_transformation_value_type($value, Collection::class);
        return $value->map(
            /**
             * @param mixed $currentValue
             *
             * @return mixed
             */
            fn($current_value) => $this->decorated_transformer->transform($current_value)
        );
    }
    /** @param Collection|null $value */
    public function reverse_transform($value): Readable_Collection
    {
        if (null === $value) {
            return new Array_Collection();
        }
        $this->assert_transformation_value_type($value, Collection::class);
        return $value->map(
            /**
             * @param mixed $currentValue
             *
             * @return mixed
             */
            fn($current_value) => $this->decorated_transformer->reverse_transform($current_value)
        );
    }
    /**
     * @param mixed $value
     *
     * @throws TransformationFailedException
     */
    private function assert_transformation_value_type($value, string $expected_type): void
    {
        if (!$value instanceof $expected_type) {
            throw new Transformation_Failed_Exception(sprintf('Expected "%s", but got "%s"', $expected_type, get_debug_type($value)));
        }
    }
}