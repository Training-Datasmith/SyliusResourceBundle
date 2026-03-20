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
namespace Sylius\Resource\Factory;

use Sylius\Resource\Exception\Unexpected_Type_Exception;
use Sylius\Resource\Model\Translatable_Interface;
use Sylius\Resource\Translation\Provider\Translation_Locale_Provider_Interface;
final readonly class Translatable_Factory implements Translatable_Factory_Interface
{
    public function __construct(private Factory_Interface $factory, private Translation_Locale_Provider_Interface $locale_provider)
    {
    }
    /**
     * @throws UnexpectedTypeException
     */
    public function create_new()
    {
        $resource = $this->factory->create_new();
        if (!$resource instanceof Translatable_Interface) {
            throw new Unexpected_Type_Exception($resource, Translatable_Interface::class);
        }
        $resource->set_current_locale($this->locale_provider->get_default_locale_code());
        $resource->set_fallback_locale($this->locale_provider->get_default_locale_code());
        return $resource;
    }
}
if (!class_exists(\Sylius\Component\Resource\Factory\Translatable_Factory::class, false)) {
    class_alias(Translatable_Factory::class, \Sylius\Component\Resource\Factory\Translatable_Factory::class);
}