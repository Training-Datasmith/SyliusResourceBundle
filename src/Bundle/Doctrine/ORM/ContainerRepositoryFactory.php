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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ORM;

use Doctrine\ORM\Entity_Manager_Interface;
use Doctrine\ORM\Entity_Repository as DoctrineEntityRepository;
use Doctrine\ORM\Mapping\Class_Metadata;
use Doctrine\ORM\Repository\Repository_Factory;
final class Container_Repository_Factory implements Repository_Factory
{
    private readonly Repository_Factory $doctrine_factory;
    /** @var DoctrineEntityRepository[] */
    private array $managed_repositories = [];
    /**
     * @param string[] $genericEntities
     */
    public function __construct(Repository_Factory $doctrine_factory, private readonly array $generic_entities)
    {
        $this->doctrine_factory = $doctrine_factory;
    }
    /**
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     */
    public function get_repository(Entity_Manager_Interface $entity_manager, $entity_name): Doctrine_Entity_Repository
    {
        $metadata = $entity_manager->get_class_metadata($entity_name);
        if ($metadata->custom_repository_class_name === null && in_array($entity_name, $this->generic_entities, true)) {
            /** @psalm-suppress InvalidReturnStatement */
            return $this->get_or_create_repository($entity_manager, $metadata);
        }
        /** @var DoctrineEntityRepository $repository */
        $repository = $this->doctrine_factory->get_repository($entity_manager, $entity_name);
        return $repository;
    }
    private function get_or_create_repository(Entity_Manager_Interface $entity_manager, Class_Metadata $metadata): Doctrine_Entity_Repository
    {
        $repository_hash = $metadata->get_name() . spl_object_hash($entity_manager);
        if (!isset($this->managed_repositories[$repository_hash])) {
            $this->managed_repositories[$repository_hash] = new Entity_Repository($entity_manager, $metadata);
        }
        return $this->managed_repositories[$repository_hash];
    }
}