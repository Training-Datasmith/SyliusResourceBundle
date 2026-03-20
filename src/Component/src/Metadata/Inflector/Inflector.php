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
namespace Sylius\Resource\Metadata\Inflector;

use Symfony\Component\String\Inflector\English_Inflector;
use Symfony\Component\String\Unicode_String;
final class Inflector implements Inflector_Interface
{
    public function tableize(string $string): string
    {
        return (new Unicode_String($string))->snake()->to_string();
    }
    public function pluralize(string $string): string
    {
        $pluralize = (new English_Inflector())->pluralize($string);
        return $pluralize ? array_pop($pluralize) : '';
    }
    public function dashize(string $string): string
    {
        return strtr($this->tableize($string), ['_' => '-']);
    }
}