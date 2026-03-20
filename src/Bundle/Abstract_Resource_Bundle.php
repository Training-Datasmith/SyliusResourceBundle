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
namespace Sylius\Bundle\Resource_Bundle;

use Doctrine\Bundle\Doctrine_Bundle\Dependency_Injection\Compiler\Doctrine_Orm_Mappings_Pass;
use Doctrine\Bundle\Mongo_Db_Bundle\Dependency_Injection\Compiler\Doctrine_Mongo_Db_Mappings_Pass;
use Doctrine\Bundle\Phpcr_Bundle\Dependency_Injection\Compiler\Doctrine_Phpcr_Mappings_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Driver\Exception\Unknown_Driver_Exception;
use Symfony\Component\Config\Definition\Exception\Invalid_Configuration_Exception;
use Symfony\Component\Dependency_Injection\Container;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Http_Kernel\Bundle\Bundle;
abstract class Abstract_Resource_Bundle extends Bundle implements Resource_Bundle_Interface
{
    /**
     * Configure format of mapping files.
     */
    protected string $mapping_format = Resource_Bundle_Interface::MAPPING_XML;
    public function build(Container_Builder $container): void
    {
        if (null !== $this->get_model_namespace()) {
            foreach ($this->get_supported_drivers() as $driver) {
                [$compiler_pass_class_name, $compiler_pass_method] = $this->get_mapping_compiler_pass_info($driver);
                if (class_exists($compiler_pass_class_name)) {
                    if (!method_exists($compiler_pass_class_name, $compiler_pass_method)) {
                        throw new Invalid_Configuration_Exception("The 'mappingFormat' value is invalid, must be 'xml', 'yaml' or 'annotation'.");
                    }
                    switch ($this->mapping_format) {
                        case Resource_Bundle_Interface::MAPPING_XML:
                        case Resource_Bundle_Interface::MAPPING_YAML:
                            $container->add_compiler_pass($compiler_pass_class_name::$compiler_pass_method([$this->get_config_files_path() => $this->get_model_namespace()], [$this->get_object_manager_parameter()], sprintf('%s.driver.%s', $this->get_bundle_prefix(), $driver)));
                            break;
                        case Resource_Bundle_Interface::MAPPING_ANNOTATION:
                            $container->add_compiler_pass($compiler_pass_class_name::$compiler_pass_method([$this->get_model_namespace()], [$this->get_config_files_path()], [sprintf('%s.object_manager', $this->get_bundle_prefix())], sprintf('%s.driver.%s', $this->get_bundle_prefix(), $driver)));
                            break;
                    }
                }
            }
        }
    }
    /**
     * Return the prefix of the bundle.
     */
    protected function get_bundle_prefix(): string
    {
        return Container::underscore(substr((string) strrchr(static::class, '\\'), 1, -6));
    }
    /**
     * Return the directory where are stored the doctrine mapping.
     */
    protected function get_doctrine_mapping_directory(): string
    {
        return 'model';
    }
    /**
     * Return the entity namespace.
     */
    protected function get_model_namespace(): ?string
    {
        return (new \ReflectionClass($this))->get_namespace_name() . '\Model';
    }
    /**
     * Return mapping compiler pass class depending on driver.
     *
     *
     *
     * @throws UnknownDriverException
     */
    protected function get_mapping_compiler_pass_info(string $driver_type): array
    {
        switch ($driver_type) {
            case Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM:
                trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" driver is deprecated. Doctrine MongoDB and PHPCR will no longer be supported in 2.0.', Sylius_Resource_Bundle::DRIVER_DOCTRINE_MONGODB_ODM);
                $mappings_pass_classname = Doctrine_Mongo_Db_Mappings_Pass::class;
                break;
            case Sylius_Resource_Bundle::DRIVER_DOCTRINE_ORM:
                $mappings_pass_classname = Doctrine_Orm_Mappings_Pass::class;
                break;
            case Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM:
                trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" driver is deprecated. Doctrine MongoDB and PHPCR will no longer be supported in 2.0.', Sylius_Resource_Bundle::DRIVER_DOCTRINE_PHPCR_ODM);
                $mappings_pass_classname = Doctrine_Phpcr_Mappings_Pass::class;
                break;
            default:
                throw new Unknown_Driver_Exception($driver_type);
        }
        $compiler_pass_method = sprintf('create%sMappingDriver', ucfirst($this->mapping_format));
        return [$mappings_pass_classname, $compiler_pass_method];
    }
    /**
     * Return the absolute path where are stored the doctrine mapping.
     */
    protected function get_config_files_path(): string
    {
        return sprintf('%s/Resources/config/doctrine/%s', $this->get_path(), strtolower($this->get_doctrine_mapping_directory()));
    }
    protected function get_object_manager_parameter(): string
    {
        return sprintf('%s.object_manager', $this->get_bundle_prefix());
    }
}