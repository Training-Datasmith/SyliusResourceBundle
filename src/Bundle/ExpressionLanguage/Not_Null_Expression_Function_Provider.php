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
namespace Sylius\Bundle\Resource_Bundle\Expression_Language;

use Symfony\Component\Expression_Language\Expression_Function;
use Symfony\Component\Expression_Language\Expression_Function_Provider_Interface;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
final class Not_Null_Expression_Function_Provider implements Expression_Function_Provider_Interface
{
    public function get_functions(): array
    {
        return [new Expression_Function(
            'notFoundOnNull',
            /**
             * @param mixed $result
             */
            fn($result): string => sprintf('(null !== %1$s) ? %1$s : throw new NotFoundHttpException(\'Requested page is invalid.\')', $result),
            /**
             * @param mixed $arguments
             * @param mixed $result
             *
             * @return mixed
             */
            function ($arguments, $result) {
                if (null === $result) {
                    throw new Not_Found_Http_Exception('Requested page is invalid.');
                }
                return $result;
            }
        )];
    }
}