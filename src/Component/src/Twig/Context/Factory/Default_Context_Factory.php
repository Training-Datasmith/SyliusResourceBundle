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
namespace Sylius\Resource\Twig\Context\Factory;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Collection_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
/**
 * @experimental
 */
final class Default_Context_Factory implements Context_Factory_Interface
{
    public function create(mixed $data, Operation $operation, Context $context): array
    {
        $twig_context = ['operation' => $operation, 'resource_metadata' => $operation->get_resource()];
        if ($operation instanceof Collection_Operation_Interface) {
            $twig_context['resources'] = $data;
            $plural_name = $operation->get_resource()?->get_plural_name();
            if (null !== $plural_name) {
                $twig_context[$plural_name] = $data;
            }
        } else {
            $twig_context['resource'] = $data;
            $name = $operation->get_resource()?->get_name();
            if (null !== $name) {
                $twig_context[$name] = $data;
            }
        }
        return $twig_context;
    }
}