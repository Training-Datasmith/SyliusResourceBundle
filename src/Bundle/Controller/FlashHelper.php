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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Session\Flash\Flash_Bag_Interface;
use Symfony\Component\Http_Foundation\Session\Session_Interface;
use function Symfony\Component\String\u;
use Symfony\Component\Translation\Translator_Bag_Interface;
use Symfony\Contracts\Translation\Translator_Interface;
final class Flash_Helper implements Flash_Helper_Interface
{
    private readonly \Symfony\Component\Http_Foundation\Request_Stack|\Symfony\Component\Http_Foundation\Session\Session_Interface $request_stack;
    private readonly Translator_Interface $translator;
    /**
     * @param RequestStack|SessionInterface $requestStack
     */
    public function __construct(
        /* RequestStack */
        $request_stack,
        Translator_Interface $translator,
        private readonly string $default_locale
    )
    {
        /** @phpstan-ignore-next-line */
        if (!$request_stack instanceof Session_Interface && !$request_stack instanceof Request_Stack) {
            throw new \InvalidArgumentException(sprintf('The first argument of "%s" should be instance of "%s" or "%s"', __METHOD__, Session_Interface::class, Request_Stack::class));
        }
        if ($request_stack instanceof Session_Interface) {
            trigger_deprecation('sylius/resource-bundle', '1.10', 'Passing an instance of "%s" as the first constructor argument for "%s" is deprecated and will not be supported in 2.0. Pass an instance of "%s" instead.', Session_Interface::class, self::class, Request_Stack::class);
        }
        $this->request_stack = $request_stack;
        $this->translator = $translator;
    }
    public function add_success_flash(Request_Configuration $request_configuration, string $action_name, ?Resource_Interface $resource = null): void
    {
        $this->add_flash_with_type($request_configuration, $action_name, 'success');
    }
    public function add_error_flash(Request_Configuration $request_configuration, string $action_name): void
    {
        $this->add_flash_with_type($request_configuration, $action_name, 'error');
    }
    public function add_flash_from_event(Request_Configuration $request_configuration, Resource_Controller_Event $event): void
    {
        $this->add_flash($event->get_message_type(), $event->get_message(), $event->get_message_parameters());
    }
    private function add_flash_with_type(Request_Configuration $request_configuration, string $action_name, string $type): void
    {
        $metadata = $request_configuration->get_metadata();
        $parameters = $this->get_parameters_with_name($metadata, $action_name);
        $message = (string) $request_configuration->get_flash_message($action_name);
        if (empty($message)) {
            return;
        }
        if ($this->is_translation_defined($message, $this->default_locale, $parameters)) {
            if (!$this->translator instanceof Translator_Bag_Interface) {
                $this->add_flash($type, $message, $parameters);
                return;
            }
            $this->add_flash($type, $message);
            return;
        }
        $this->add_flash($type, $this->get_resource_message($action_name), $parameters);
    }
    private function add_flash(string $type, string $message, array $parameters = []): void
    {
        if (!empty($parameters)) {
            $message = $this->prepare_message($message, $parameters);
        }
        if ($this->request_stack instanceof Session_Interface) {
            $session = $this->request_stack;
        } else {
            $session = $this->request_stack->get_session();
        }
        /** @var FlashBagInterface $flashBag */
        $flash_bag = $session->get_bag('flashes');
        $flash_bag->add($type, $message);
    }
    private function prepare_message(string $message, array $parameters): array
    {
        return ['message' => $message, 'parameters' => $parameters];
    }
    private function get_resource_message(string $action_name): string
    {
        return sprintf('sylius.resource.%s', $action_name);
    }
    private function is_translation_defined(string $message, string $locale, array $parameters): bool
    {
        if ($this->translator instanceof Translator_Bag_Interface) {
            $default_catalogue = $this->translator->get_catalogue($locale);
            return $default_catalogue->has($message, 'flashes');
        }
        return $message !== $this->translator->trans($message, $parameters, 'flashes');
    }
    private function get_parameters_with_name(Metadata_Interface $metadata, string $action_name): array
    {
        $application_name = $metadata->get_application_name();
        if (stripos($action_name, 'bulk') !== false) {
            $resource_name = $metadata->get_plural_name();
            $fallback = ucfirst($resource_name);
            return ['%resources%' => $this->translate_resource_name($application_name, $resource_name, $fallback)];
        }
        $resource_name = $metadata->get_name();
        $fallback = ucfirst($metadata->get_humanized_name());
        return ['%resource%' => $this->translate_resource_name($application_name, $resource_name, $fallback)];
    }
    private function translate_resource_name(string $application_name, string $resource_name, string $fallback): string
    {
        $snake_case_name = u($resource_name)->snake()->to_string();
        $translation_key = sprintf('%s.ui.%s', $application_name, $snake_case_name);
        $translated = $this->translator->trans($translation_key, [], 'messages');
        return $translated === $translation_key ? $fallback : $translated;
    }
}