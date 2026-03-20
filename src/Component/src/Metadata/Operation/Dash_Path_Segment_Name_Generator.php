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
namespace Sylius\Resource\Metadata\Operation;

use Sylius\Resource\Metadata\Inflector\Inflector_Interface;
/**
 * Generate a path name with a dash separator according to a string and whether it's a collection or not.
 */
final readonly class Dash_Path_Segment_Name_Generator implements Path_Segment_Name_Generator_Interface
{
    public function __construct(private Inflector_Interface $inflector)
    {
    }
    /**
     * @inheritdoc
     */
    public function get_segment_name(string $name, bool $pluralize = true): string
    {
        return $pluralize ? $this->inflector->dashize($this->inflector->pluralize($name)) : $this->inflector->dashize($name);
    }
}