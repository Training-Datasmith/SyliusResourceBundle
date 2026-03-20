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
namespace Sylius\Resource\Symfony\Routing\Factory\Route_Path;

use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation\Path_Segment_Name_Generator_Interface;
/**
 * @experimental
 */
final readonly class Bulk_Operation_Route_Path_Factory implements Operation_Route_Path_Factory_Interface
{
    public function __construct(private Operation_Route_Path_Factory_Interface $decorated, private Path_Segment_Name_Generator_Interface $path_segment_name_generator)
    {
    }
    public function create_route_path(Http_Operation $operation, string $root_path): string
    {
        $short_name = $operation->get_short_name() ?? '';
        if ($operation instanceof Bulk_Operation_Interface) {
            return sprintf('%s/%s', $root_path, $this->path_segment_name_generator->get_segment_name($short_name, false));
        }
        return $this->decorated->create_route_path($operation, $root_path);
    }
}