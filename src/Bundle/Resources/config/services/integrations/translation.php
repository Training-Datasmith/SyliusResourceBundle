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

use Sylius\Bundle\Resource_Bundle\Event_Listener\Orm_Translatable_Listener;
use Sylius\Bundle\Resource_Bundle\Form\Type\Resource_Translations_Type;
use Sylius\Bundle\Resource_Bundle\Form\Type\Resource_Translations_Type as ResourceTranslationsTypeInterface;
use Sylius\Component\Resource\Translation\Provider\Immutable_Translation_Locale_Provider as ComponentImmutableTranslationLocaleProvider;
use Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner;
use Sylius\Component\Resource\Translation\Translatable_Entity_Locale_Assigner_Interface;
use Sylius\Resource\Translation\Provider\Immutable_Translation_Locale_Provider as ResourceImmutableTranslationLocaleProvider;
use Sylius\Resource\Translation\Provider\Translation_Locale_Provider_Interface;
return static function (Container_Configurator $container): void {
    $services = $container->services();
    $services->defaults()->public();
    $services->set('sylius.translation_locale_provider.immutable', Resource_Immutable_Translation_Locale_Provider::class)->args([['' => '%locale%'], '%locale%']);
    $services->alias(Component_Immutable_Translation_Locale_Provider::class, 'sylius.translation_locale_provider.immutable')->deprecate('sylius/resource-bundle', '1.11', 'The "%alias_id%" service alias is deprecated since sylius/resource-bundle 1.11 and will be removed in sylius/resource-bundle 2.0. Use Sylius\Resource\Translation\Provider\ImmutableTranslationLocaleProvider instead.');
    $services->alias(Resource_Immutable_Translation_Locale_Provider::class, 'sylius.translation_locale_provider.immutable');
    $services->alias(Translation_Locale_Provider_Interface::class, 'sylius.translation_locale_provider.immutable');
    $services->set('sylius.translation.translatable_listener.doctrine.orm', Orm_Translatable_Listener::class)->args([service('sylius.resource_registry'), service('sylius.translatable_entity_locale_assigner')])->tag('doctrine.event_listener', ['connection' => 'default', 'event' => 'loadClassMetadata', 'priority' => 99])->tag('doctrine.event_listener', ['connection' => 'default', 'event' => 'postLoad', 'priority' => 99]);
    $services->alias(Orm_Translatable_Listener::class, 'sylius.translation.translatable_listener.doctrine.orm');
    $services->set('sylius.form.type.resource_translations', Resource_Translations_Type::class)->args([service('sylius.translation_locale_provider')])->tag('form.type');
    $services->alias(Resource_Translations_Type_Interface::class, 'sylius.form.type.resource_translations');
    $services->set('sylius.translatable_entity_locale_assigner', Translatable_Entity_Locale_Assigner::class)->args([service('sylius.translation_locale_provider')]);
    $services->alias(Translatable_Entity_Locale_Assigner_Interface::class, 'sylius.translatable_entity_locale_assigner');
};