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
namespace Sylius\Resource\Metadata\Resource\Factory;

use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
final class Templates_Dir_Resource_Metadata_Collection_Factory implements Resource_Metadata_Collection_Factory_Interface
{
    public function __construct(private readonly Resource_Metadata_Collection_Factory_Interface $decorated, private array $settings = [])
    {
    }
    public function create(string $resource_class): Resource_Metadata_Collection
    {
        $resource_collection_metadata = $this->decorated->create($resource_class);
        /** @var ResourceMetadata $resource */
        foreach ($resource_collection_metadata->getIterator() as $i => $resource) {
            $operations = $resource->get_operations() ?? new Operations();
            /** @var Operation $operation */
            foreach ($operations as $operation) {
                /** @var string $key */
                $key = $operation->get_name();
                $operations->add($key, $this->add_defaults($resource, $operation));
            }
            $resource = $resource->with_operations($operations);
            $resource_collection_metadata[$i] = $resource;
        }
        return $resource_collection_metadata;
    }
    private function add_defaults(Resource_Metadata $resource, Operation $operation): Operation
    {
        if (null === $operation->get_template()) {
            $template_dir = $resource->get_templates_dir() ?? $this->settings['default_templates_dir'] ?? '';
            $template = sprintf('%s/%s.html.twig', $template_dir, $operation->get_short_name() ?? '');
            $operation = $operation->with_template($template);
        }
        return $operation;
    }
}