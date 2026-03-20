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

use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Csrf_Token_Manager_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Doctrine_Container_Repository_Factory_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Doctrine_Target_Entities_Resolver_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Helper\Target_Entities_Resolver;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Pagerfanta_Bridge_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_Form_Builder_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_Fqcn_Controllers_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_Resource_Repository_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_Resources_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_Resource_State_Machine_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Register_State_Machine_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Twig_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Unregister_Fos_Rest_Definitions_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Unregister_Hateoas_Definitions_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Compiler\Winzou_State_Machine_Pass;
use Sylius\Bundle\Resource_Bundle\Dependency_Injection\Pagerfanta_Extension;
use Sylius\Resource\Symfony\Dependency_Injection\Compiler\Disable_Metadata_Cache_Pass;
use Sylius\Resource\Symfony\Dependency_Injection\Compiler\Fallback_To_Kernel_Default_Locale_Pass;
use Sylius\Resource\Symfony\Dependency_Injection\Compiler\Metadata_Mutator_Pass;
use Symfony\Component\Dependency_Injection\Compiler\Pass_Config;
use Symfony\Component\Dependency_Injection\Container_Builder;
use Symfony\Component\Http_Kernel\Bundle\Bundle;
final class Sylius_Resource_Bundle extends Bundle
{
    public const DRIVER_DOCTRINE_ORM = 'doctrine/orm';
    public const DRIVER_DOCTRINE_MONGODB_ODM = 'doctrine/mongodb-odm';
    public const DRIVER_DOCTRINE_PHPCR_ODM = 'doctrine/phpcr-odm';
    public const NO_DRIVER = false;
    public function build(Container_Builder $container): void
    {
        parent::build($container);
        $container->add_compiler_pass(new Csrf_Token_Manager_Pass());
        $container->add_compiler_pass(new Disable_Metadata_Cache_Pass());
        $container->add_compiler_pass(new Fallback_To_Kernel_Default_Locale_Pass());
        $container->add_compiler_pass(new Doctrine_Container_Repository_Factory_Pass());
        $container->add_compiler_pass(new Doctrine_Target_Entities_Resolver_Pass(new Target_Entities_Resolver()), Pass_Config::TYPE_BEFORE_OPTIMIZATION, 1);
        $container->add_compiler_pass(new Metadata_Mutator_Pass());
        $container->add_compiler_pass(new Register_Form_Builder_Pass());
        $container->add_compiler_pass(new Register_Fqcn_Controllers_Pass());
        $container->add_compiler_pass(new Register_Resource_Repository_Pass());
        $container->add_compiler_pass(new Register_Resources_Pass());
        $container->add_compiler_pass(new Register_State_Machine_Pass());
        $container->add_compiler_pass(new Register_Resource_State_Machine_Pass());
        $container->add_compiler_pass(new Unregister_Fos_Rest_Definitions_Pass());
        $container->add_compiler_pass(new Unregister_Hateoas_Definitions_Pass());
        $container->add_compiler_pass(new Twig_Pass());
        $container->add_compiler_pass(new Winzou_State_Machine_Pass());
        $container->register_extension(new Pagerfanta_Extension(true));
        $container->add_compiler_pass(new Pagerfanta_Bridge_Pass(true), Pass_Config::TYPE_BEFORE_OPTIMIZATION, -1);
        // Should run after all passes from BabDevPagerfantaBundle
    }
    /**
     * @return string[]
     */
    public static function get_available_drivers(): array
    {
        return [self::DRIVER_DOCTRINE_ORM, self::DRIVER_DOCTRINE_MONGODB_ODM, self::DRIVER_DOCTRINE_PHPCR_ODM];
    }
}