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

use Sylius\Bundle\Resource_Bundle\Provider\Request_Parameter_Provider;
use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Property_Access\Property_Access;
class Request_Configuration
{
    private readonly Request $request;
    public function __construct(private readonly Metadata_Interface $metadata, Request $request, private readonly Parameters $parameters)
    {
        $this->request = $request;
    }
    public function get_request(): \Symfony\Component\Http_Foundation\Request
    {
        return $this->request;
    }
    public function get_metadata(): \Sylius\Resource\Metadata\Metadata_Interface
    {
        return $this->metadata;
    }
    public function get_parameters(): \Sylius\Bundle\Resource_Bundle\Controller\Parameters
    {
        return $this->parameters;
    }
    /**
     * @return string|null
     */
    public function get_section(): mixed
    {
        return $this->parameters->get('section');
    }
    public function is_html_request(): bool
    {
        return 'html' === $this->request->get_request_format();
    }
    /**
     * @param string $name
     */
    public function get_default_template($name): string
    {
        $templates_namespace = (string) $this->metadata->get_templates_namespace();
        if (str_contains($templates_namespace, ':')) {
            return sprintf('%s:%s.%s', $templates_namespace, $name, 'twig');
        }
        return sprintf('%s/%s.%s', $templates_namespace, $name, 'twig');
    }
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get_template($name)
    {
        $template = $this->parameters->get('template', $this->get_default_template($name));
        if (null === $template) {
            throw new \RuntimeException(sprintf('Could not resolve template for resource "%s".', $this->metadata->get_alias()));
        }
        return $template;
    }
    /**
     * @return string|null
     */
    public function get_form_type()
    {
        $form = $this->parameters->get('form');
        if (isset($form['type'])) {
            return $form['type'];
        }
        if (is_string($form)) {
            return $form;
        }
        return $this->metadata->get_class('form');
    }
    /**
     * @return array
     */
    public function get_form_options()
    {
        $form = $this->parameters->get('form');
        return $form['options'] ?? [];
    }
    /**
     * @param string $name
     */
    public function get_route_name($name): string
    {
        $section = $this->get_section();
        $section_prefix = $section ? $section . '_' : '';
        return sprintf('%s_%s%s_%s', $this->metadata->get_application_name(), $section_prefix, $this->metadata->get_name(), $name);
    }
    /**
     * @param string $name
     *
     * @return mixed|string|null
     */
    public function get_redirect_route($name)
    {
        $redirect = $this->parameters->get('redirect');
        if (null === $redirect) {
            return $this->get_route_name($name);
        }
        if (is_array($redirect)) {
            if (!empty($redirect['referer'])) {
                return 'referer';
            }
            return $redirect['route'];
        }
        return $redirect;
    }
    /**
     * Get url hash fragment (#text) which is you configured.
     */
    public function get_redirect_hash(): string
    {
        $redirect = $this->parameters->get('redirect');
        if (!is_array($redirect) || empty($redirect['hash'])) {
            return '';
        }
        return '#' . $redirect['hash'];
    }
    /**
     * Get redirect referer, This will detected by configuration
     * If not exists, The `referrer` from headers will be used.
     *
     * @return string|null
     */
    public function get_redirect_referer()
    {
        /** @var array|null $redirect */
        $redirect = $this->parameters->get('redirect');
        /** @var string|null $referer */
        $referer = $this->request->headers->get('referer');
        if (!is_array($redirect) || empty($redirect['referer'])) {
            return $referer;
        }
        if ($redirect['referer'] === true) {
            return $referer;
        }
        return $redirect['referer'];
    }
    /**
     * @param object|null $resource
     */
    public function get_redirect_parameters($resource = null): array
    {
        $redirect = $this->parameters->get('redirect');
        if (isset($redirect['parameters']) && $redirect['parameters'] === []) {
            return [];
        }
        if (!is_array($redirect)) {
            $redirect = ['parameters' => []];
        }
        $parameters = $redirect['parameters'] ?? [];
        $parameters = $this->add_extra_redirect_parameters($parameters);
        if (null !== $resource) {
            return $this->parse_resource_values($parameters, $resource);
        }
        return $parameters;
    }
    /**
     * @param array $parameters
     */
    private function add_extra_redirect_parameters($parameters): array
    {
        $vars = $this->get_vars();
        $accessor = Property_Access::create_property_accessor();
        if ($accessor->is_readable($vars, '[redirect][parameters]')) {
            $extra_parameters = $accessor->get_value($vars, '[redirect][parameters]');
            if (is_array($extra_parameters)) {
                $parameters = array_merge($parameters, $extra_parameters);
            }
        }
        return $parameters;
    }
    public function is_limited(): bool
    {
        return (bool) $this->parameters->get('limit', false);
    }
    public function get_limit(): ?int
    {
        if ($this->is_limited()) {
            return (int) $this->parameters->get('limit', 10);
        }
        return null;
    }
    public function is_paginated(): bool
    {
        $pagination = $this->parameters->get('paginate', true);
        return $pagination !== false && $pagination !== null;
    }
    public function get_pagination_max_per_page(): int
    {
        return (int) $this->parameters->get('paginate', 10);
    }
    public function is_filterable(): bool
    {
        return (bool) $this->parameters->get('filterable', false);
    }
    /**
     * @return array
     */
    public function get_criteria(array $criteria = [])
    {
        $default_criteria = array_merge($this->parameters->get('criteria', []), $criteria);
        if ($this->is_filterable()) {
            return $this->get_request_parameter('criteria', $default_criteria);
        }
        return $default_criteria;
    }
    public function is_sortable(): bool
    {
        return (bool) $this->parameters->get('sortable', false);
    }
    /**
     * @return array
     */
    public function get_sorting(array $sorting = [])
    {
        $default_sorting = array_merge($this->parameters->get('sorting', []), $sorting);
        if ($this->is_sortable()) {
            $sorting = $this->get_request_parameter('sorting');
            foreach ($default_sorting as $key => $value) {
                if (!isset($sorting[$key])) {
                    $sorting[$key] = $value;
                }
            }
            return $sorting;
        }
        return $default_sorting;
    }
    /**
     * @param array $defaults
     *
     */
    public function get_request_parameter(string $parameter, $defaults = []): array
    {
        return array_replace_recursive($defaults, Request_Parameter_Provider::provide($this->request, $parameter, []));
    }
    /**
     * @return array|string|null
     */
    public function get_repository_method()
    {
        if (!$this->parameters->has('repository')) {
            return null;
        }
        $repository = $this->parameters->get('repository');
        return is_array($repository) ? $repository['method'] : $repository;
    }
    public function get_repository_arguments(): array
    {
        if (!$this->parameters->has('repository')) {
            return [];
        }
        $repository = $this->parameters->get('repository');
        if (!isset($repository['arguments'])) {
            return [];
        }
        return is_array($repository['arguments']) ? $repository['arguments'] : [$repository['arguments']];
    }
    /**
     * @return array|string|null
     */
    public function get_factory_method()
    {
        if (!$this->parameters->has('factory')) {
            return null;
        }
        $factory = $this->parameters->get('factory');
        return is_array($factory) ? $factory['method'] : $factory;
    }
    public function get_factory_arguments(): array
    {
        if (!$this->parameters->has('factory')) {
            return [];
        }
        $factory = $this->parameters->get('factory');
        if (!isset($factory['arguments'])) {
            return [];
        }
        return is_array($factory['arguments']) ? $factory['arguments'] : [$factory['arguments']];
    }
    /**
     * @param string $message
     *
     * @return mixed|null
     */
    public function get_flash_message($message): mixed
    {
        return $this->parameters->get('flash', sprintf('%s.%s.%s', $this->metadata->get_application_name(), $this->metadata->get_name(), $message));
    }
    /**
     * @return mixed|null
     */
    public function get_sortable_position(): mixed
    {
        return $this->parameters->get('sortable_position', 'position');
    }
    /**
     * @return array|null
     */
    public function get_serialization_groups(): mixed
    {
        return $this->parameters->get('serialization_groups', []);
    }
    /**
     * @return mixed|null
     */
    public function get_serialization_version(): mixed
    {
        return $this->parameters->get('serialization_version');
    }
    /**
     * @return string|null
     */
    public function get_event(): mixed
    {
        return $this->parameters->get('event');
    }
    public function has_permission(): bool
    {
        return false !== $this->parameters->get('permission', false);
    }
    /**
     * @param string $name
     *
     * @return string
     *
     * @throws \LogicException
     */
    public function get_permission($name)
    {
        $permission = $this->parameters->get('permission');
        if (null === $permission) {
            throw new \LogicException('Current action does not require any authorization.');
        }
        if (true === $permission) {
            return sprintf('%s.%s.%s', $this->metadata->get_application_name(), $this->metadata->get_name(), $name);
        }
        return $permission;
    }
    /**
     * @return bool
     */
    public function is_header_redirection()
    {
        $redirect = $this->parameters->get('redirect');
        if (!is_array($redirect) || !isset($redirect['header'])) {
            return false;
        }
        if ('xhr' === $redirect['header']) {
            return $this->get_request()->is_xml_http_request();
        }
        return (bool) $redirect['header'];
    }
    /**
     * @return array
     */
    public function get_vars(): mixed
    {
        return $this->parameters->get('vars', []);
    }
    /**
     * @param object $resource
     */
    private function parse_resource_values(array $parameters, $resource): array
    {
        $accessor = Property_Access::create_property_accessor();
        if (empty($parameters)) {
            return ['id' => $accessor->get_value($resource, 'id')];
        }
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parameters[$key] = $this->parse_resource_values($value, $resource);
            }
            if (is_string($value) && str_starts_with($value, 'resource.')) {
                $parameters[$key] = $accessor->get_value($resource, substr($value, 9));
            }
        }
        return $parameters;
    }
    /**
     * @return bool
     */
    public function has_grid()
    {
        return $this->parameters->has('grid');
    }
    /**
     * @return string
     *
     * @throws \LogicException
     */
    public function get_grid(): mixed
    {
        if (!$this->has_grid()) {
            throw new \LogicException('Current action does not use grid.');
        }
        return $this->parameters->get('grid');
    }
    /**
     * @return bool
     */
    public function has_state_machine()
    {
        return $this->parameters->has('state_machine');
    }
    /**
     * @return string|null
     */
    public function get_state_machine_graph()
    {
        $options = $this->parameters->get('state_machine');
        return $options['graph'] ?? null;
    }
    /**
     * @return string|null
     */
    public function get_state_machine_transition()
    {
        $options = $this->parameters->get('state_machine');
        return $options['transition'] ?? null;
    }
    /**
     * @return bool
     */
    public function is_csrf_protection_enabled(): mixed
    {
        return $this->parameters->get('csrf_protection', true);
    }
}