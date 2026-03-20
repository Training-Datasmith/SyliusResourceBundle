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
namespace Sylius\Bundle\Resource_Bundle\Command;

use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operations;
use Sylius\Resource\Metadata\Registry_Interface;
use Sylius\Resource\Metadata\Resource\Factory\Resource_Metadata_Collection_Factory_Interface;
use Sylius\Resource\Metadata\Resource\Resource_Metadata_Collection;
use Sylius\Resource\Metadata\Resource_Metadata;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Dumper;
use Symfony\Component\Console\Input\Input_Argument;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Input\Input_Option;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Style\Symfony_Style;
use Symfony\Component\Property_Access\Property_Access;
final class Debug_Resource_Command extends Command
{
    public function __construct(private readonly Registry_Interface $registry, private readonly Resource_Metadata_Collection_Factory_Interface $resource_metadata_collection_factory)
    {
        parent::__construct();
    }
    public function configure(): void
    {
        $this->set_name('sylius:debug:resource');
        $this->set_description('Debug resource metadata.');
        $this->set_help(<<<'EOT'
        List or show resource metadata.
        
        To list run the command without an argument:
        
            $ php %command.full_name%
        
        To show the metadata for a resource, pass its alias:
        
            $ php %command.full_name% sylius.user
        EOT);
        $this->add_argument('resource', Input_Argument::OPTIONAL, 'Resource to debug');
        $this->add_argument('operation', Input_Argument::OPTIONAL, 'Operation to debug');
        $this->add_option('legacy', null, Input_Option::VALUE_NONE, 'Show legacy resource metadata.');
    }
    public function execute(Input_Interface $input, Output_Interface $output): int
    {
        /** @var string|null $resource */
        $resource = $input->get_argument('resource');
        $io = new Symfony_Style($input, $output);
        $dumper = new Dumper($output);
        if (null === $resource) {
            $this->list_resources($io);
            return Command::SUCCESS;
        }
        if (str_contains($resource, '.')) {
            $metadata = $this->registry->get($resource);
        } else {
            $metadata = $this->registry->get_by_class($resource);
        }
        $resource_metadata_collection = $this->get_resource_metadata_collection($metadata);
        /** @var string|null $operationName */
        $operation_name = $input->get_argument('operation');
        if (null !== $operation_name) {
            $operation = $resource_metadata_collection->get_operation($metadata->get_alias(), $operation_name);
            $this->debug_operation($operation, $io, $dumper);
            return Command::SUCCESS;
        }
        if ($input->get_option('legacy')) {
            $this->debug_legacy_resource_metadata($metadata, $io, $dumper);
            return Command::SUCCESS;
        }
        $this->debug_resource($metadata, $input, $io, $dumper);
        return Command::SUCCESS;
    }
    private function list_resources(Symfony_Style $io): void
    {
        /** @var iterable<MetadataInterface> $resources */
        $resources = $this->registry->get_all();
        $resources = is_array($resources) ? $resources : iterator_to_array($resources);
        ksort($resources);
        $rows = [];
        foreach ($resources as $resource) {
            $rows[] = [$resource->get_alias()];
        }
        $io->table(['Alias'], $rows);
    }
    private function debug_resource(Metadata_Interface $metadata, Input_Interface $input, Symfony_Style $io, Dumper $dumper): void
    {
        $resource_metadata_collection = $this->get_resource_metadata_collection($metadata);
        $this->debug_resource_metadata($resource_metadata_collection, $io, $dumper);
        $this->debug_resource_collection_operation($metadata, $input, $io);
    }
    private function debug_legacy_resource_metadata(Metadata_Interface $metadata, Symfony_Style $io, Dumper $dumper): void
    {
        $io->section('Configuration');
        $values = $this->configuration_to_array($metadata);
        $rows = [];
        foreach ($values as $key => $value) {
            $rows[] = [$key, $dumper($value)];
        }
        $io->table(['Option', 'Value'], $rows);
    }
    private function get_resource_metadata_collection(Metadata_Interface $resource_configuration): Resource_Metadata_Collection
    {
        return $this->resource_metadata_collection_factory->create($resource_configuration->get_class('model'));
    }
    private function debug_operation(Operation $operation, Symfony_Style $io, Dumper $dumper): void
    {
        $io->section('Operation Metadata');
        $values = $this->operation_to_array($operation);
        $rows = [];
        foreach ($values as $key => $value) {
            $rows[] = [$key, $dumper($value)];
        }
        $io->table(['Option', 'Value'], $rows);
    }
    private function debug_resource_metadata(Resource_Metadata_Collection $resource_metadata_collection, Symfony_Style $io, Dumper $dumper): void
    {
        $io->section('Resource Metadata');
        if (0 === $resource_metadata_collection->count()) {
            $io->info('This resource has no metadata.');
            return;
        }
        /** @var ResourceMetadata $resourceMetadata */
        foreach ($resource_metadata_collection as $resource_metadata) {
            $rows = [];
            $values = $this->resource_to_array($resource_metadata);
            foreach ($values as $key => $value) {
                $rows[] = [$key, $dumper($value)];
            }
            $io->table(['Option', 'Value'], $rows);
        }
    }
    private function debug_resource_collection_operation(Metadata_Interface $metadata, Input_Interface $input, Symfony_Style $io): void
    {
        $io->section('Operations');
        $resource_metadata_collection = $this->resource_metadata_collection_factory->create($metadata->get_class('model'));
        $rows = [];
        /** @var ResourceMetadata $resourceMetadata */
        foreach ($resource_metadata_collection as $resource_metadata) {
            $rows = $this->add_resource_operations_rows($resource_metadata, $rows, $input);
        }
        if ($rows === []) {
            $io->info('This resource has no defined operations.');
            return;
        }
        $io->table(['Name', 'Details'], $rows);
    }
    private function add_resource_operations_rows(Resource_Metadata $resource_metadata, array $rows, Input_Interface $input): array
    {
        /** @var string $resourceName */
        $resource_name = $input->get_argument('resource');
        /** @var Operation $operation */
        foreach ($resource_metadata->get_operations() ?? new Operations() as $operation) {
            $rows[] = [$operation->get_name(), sprintf('<comment>bin/console %s %s %s</comment>', $this->get_name() ?? '', $resource_name, $operation->get_name() ?? '')];
        }
        return $rows;
    }
    private function configuration_to_array(Metadata_Interface $metadata): array
    {
        $values = $this->object_to_array($metadata);
        $values = array_merge($values, $values['parameters']);
        unset($values['parameters']);
        return $values;
    }
    private function resource_to_array(Resource_Metadata $resource): array
    {
        $values = $this->object_to_array($resource);
        unset($values['operations']);
        return $values;
    }
    private function operation_to_array(Operation $operation): array
    {
        return $this->object_to_array($operation);
    }
    private function object_to_array(object $object): array
    {
        $accessor = Property_Access::create_property_accessor();
        $reflection = new \ReflectionClass($object);
        $values = [];
        foreach ($reflection->get_properties() as $property) {
            $property_name = $property->get_name();
            if ($accessor->is_readable($object, $property_name)) {
                $values[$property->get_name()] = $accessor->get_value($object, $property_name);
            }
        }
        return $values;
    }
}