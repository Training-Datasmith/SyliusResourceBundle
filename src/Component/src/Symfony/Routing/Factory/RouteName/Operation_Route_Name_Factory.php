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
namespace Sylius\Resource\Symfony\Routing\Factory\Route_Name;

use Sylius\Resource\Metadata\Operation;
/**
 * @experimental
 */
final class Operation_Route_Name_Factory implements Operation_Route_Name_Factory_Interface
{
    public function create_route_name(Operation $operation, ?string $short_name = null): string
    {
        $resource = $operation->get_resource();
        if (null === $resource) {
            throw new \RuntimeException(sprintf('No resource was found on the operation "%s"', $operation->get_short_name() ?? ''));
        }
        $section = $resource->get_section();
        $section_prefix = $section ? $section . '_' : '';
        return sprintf('%s_%s%s_%s', $resource->get_application_name() ?? '', $section_prefix, $resource->get_name() ?? '', $short_name ?? $operation->get_short_name() ?? '');
    }
}