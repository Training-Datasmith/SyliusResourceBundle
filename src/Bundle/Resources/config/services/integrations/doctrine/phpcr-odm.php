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
namespace Symfony\Component\Dependency_Injection\Loader\Configurator;

use Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Document_Repository;
return static function (Container_Configurator $container): void {
    $parameters = $container->parameters();
    $parameters->set('sylius.phpcr_odm.repository.class', Document_Repository::class);
};