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
namespace Sylius\Resource\Symfony\Session\Flash;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Humanizer\String_Humanizer;
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Resource_Metadata;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\Symfony\Event_Dispatcher\Generic_Event;
use Symfony\Component\Http_Foundation\Session\Flash\Flash_Bag_Interface;
use Symfony\Component\Translation\Translator_Bag_Interface;
use Symfony\Contracts\Translation\Translator_Interface;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Flash_Helper implements Flash_Helper_Interface
{
    public function __construct(private Translator_Interface $translator)
    {
    }
    public function add_success_flash(Operation $operation, Context $context, ?string $message = null): void
    {
        $this->add_flash_from_operation($operation, $context, 'success', $message);
    }
    public function add_error_flash(Operation $operation, Context $context, ?string $message = null): void
    {
        $this->add_flash_from_operation($operation, $context, 'error', $message);
    }
    public function add_flash_from_event(Generic_Event $event, Context $context): void
    {
        $message = $this->build_event_message($event);
        $this->add_flash($message, $event->get_message_type(), $context);
    }
    private function add_flash_from_operation(Operation $operation, Context $context, string $type, ?string $message): void
    {
        $message ??= $this->build_operation_message($operation, $type);
        $this->add_flash($message, $type, $context);
    }
    private function build_event_message(Generic_Event $event): string
    {
        $message = $event->get_message();
        $parameters = $event->get_message_parameters();
        if (!$this->translator instanceof Translator_Bag_Interface) {
            return $this->translator->trans($message, $parameters, 'flashes');
        }
        if ($this->translator->get_catalogue()->has($message, 'flashes')) {
            return $this->translator->trans($message, $parameters, 'flashes');
        }
        return $message;
    }
    private function build_operation_message(Operation $operation, string $type): string
    {
        $resource = $operation->get_resource();
        Assert::not_null($resource);
        $translation_keys = iterator_to_array($this->get_translation_keys($resource, $operation, $type));
        /** @var string $firstTranslationKey */
        $first_translation_key = reset($translation_keys);
        $parameters = $this->get_translation_parameters($operation);
        $notification_message = $operation->get_notification_message();
        // It's defined by the user, it should be used.
        if ('success' === $type && null !== $notification_message) {
            // Do not use the translator if not needed
            if ($this->translator instanceof Translator_Bag_Interface && !$this->translator->get_catalogue()->has($notification_message, 'flashes')) {
                return $notification_message;
            }
            return $this->translator->trans($notification_message, $parameters, 'flashes');
        }
        if (!$this->translator instanceof Translator_Bag_Interface) {
            return $this->translator->trans($first_translation_key, $parameters, 'flashes');
        }
        foreach ($translation_keys as $translation_key) {
            if ($this->translator->get_catalogue()->has($translation_key, 'flashes')) {
                return $this->translator->trans($translation_key, $parameters, 'flashes');
            }
        }
        // Last fallback, use the first translation key.
        return $this->translator->trans($first_translation_key, $parameters, 'flashes');
    }
    private function add_flash(string $message, string $type, Context $context): void
    {
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return;
        }
        /** @var FlashBagInterface $flashBag */
        $flash_bag = $request->get_session()->get_bag('flashes');
        $flash_bag->add($type, $message);
    }
    private function get_translation_parameters(Operation $operation): array
    {
        $resource = $operation->get_resource();
        if (null === $resource) {
            return [];
        }
        $resource_name = $this->translate_resource($resource);
        $humanized_name = $resource_name ?? ucfirst(String_Humanizer::humanize($resource->get_name() ?? ''));
        if ($operation instanceof Bulk_Operation_Interface) {
            $resource_plural_name = $this->translate_resource($resource, true);
            $humanized_plural_name = $resource_plural_name ?? ucfirst(String_Humanizer::humanize($resource->get_plural_name() ?? ''));
            return ['%resource%' => $humanized_name, '%resources%' => $humanized_plural_name];
        }
        return ['%resource%' => $humanized_name];
    }
    private function translate_resource(Resource_Metadata $resource, bool $plurialize = false): ?string
    {
        $translation_key = sprintf('%s.ui.%s', $resource->get_application_name() ?? '', $plurialize ? $resource->get_plural_name() ?? '' : $resource->get_name() ?? '');
        if ($this->translator instanceof Translator_Bag_Interface && $this->translator->get_catalogue()->has($translation_key)) {
            return $this->translator->trans($translation_key);
        }
        return null;
    }
    /**
     * @return iterable<string>
     */
    private function get_translation_keys(Resource_Metadata $resource, Operation $operation, string $type): iterable
    {
        $application_name = $resource->get_application_name() ?? '';
        $resource_name = $resource->get_name() ?? '';
        $operation_short_name = $operation->get_short_name() ?? '';
        $translation_key_suffix = 'error' === $type ? '_error' : '';
        /**
         * Examples:
         * app.product.my_operation
         * app.product.my_operation_error
         */
        yield sprintf('%s.%s.%s%s', $application_name, $resource_name, $operation_short_name, $translation_key_suffix);
        $generic_operation_type = $this->get_generic_operation_type($operation);
        /**
         * Examples:
         * app.product.delete
         * app.product.delete_error
         */
        if ($generic_operation_type !== $operation_short_name) {
            yield sprintf('%s.%s.%s%s', $application_name, $resource_name, $generic_operation_type, $translation_key_suffix);
        }
        /**
         * Examples:
         * sylius.resource.my_operation
         * sylius.resource.my_operation_error
         */
        yield sprintf('sylius.resource.%s%s', $operation_short_name, $translation_key_suffix);
        /**
         * Examples:
         * sylius.resource.delete
         * sylius.resource.delete_error
         */
        if ($generic_operation_type !== $operation_short_name) {
            yield sprintf('sylius.resource.%s%s', $generic_operation_type, $translation_key_suffix);
        }
    }
    private function get_generic_operation_type(Operation $operation): ?string
    {
        if ($operation instanceof Delete_Operation_Interface) {
            return 'delete';
        }
        if ($operation instanceof Create_Operation_Interface) {
            return 'create';
        }
        if ($operation instanceof Update_Operation_Interface) {
            return 'update';
        }
        return null;
    }
}