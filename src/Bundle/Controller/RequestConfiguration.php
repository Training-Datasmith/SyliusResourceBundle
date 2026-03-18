<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ResourceBundle\Controller;

use Sylius\Bundle\ResourceBundle\Provider\RequestParameterProvider;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RequestConfiguration
{
    private readonly Request $request;

    public function __construct(private readonly MetadataInterface $metadata, Request $request, private readonly Parameters $parameters)
    {
        $this->request = $request;
    }

    public function getRequest(): \Symfony\Component\HttpFoundation\Request
    {
        return $this->request;
    }

    public function getMetadata(): \Sylius\Resource\Metadata\MetadataInterface
    {
        return $this->metadata;
    }

    public function getParameters(): \Sylius\Bundle\ResourceBundle\Controller\Parameters
    {
        return $this->parameters;
    }

    /**
     * @return string|null
     */
    public function getSection(): mixed
    {
        return $this->parameters->get('section');
    }

    public function isHtmlRequest(): bool
    {
        return 'html' === $this->request->getRequestFormat();
    }

    /**
     * @param string $name
     */
    public function getDefaultTemplate($name): string
    {
        $templatesNamespace = (string) $this->metadata->getTemplatesNamespace();

        if (str_contains($templatesNamespace, ':')) {
            return sprintf('%s:%s.%s', $templatesNamespace, $name, 'twig');
        }

        return sprintf('%s/%s.%s', $templatesNamespace, $name, 'twig');
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getTemplate($name)
    {
        $template = $this->parameters->get('template', $this->getDefaultTemplate($name));

        if (null === $template) {
            throw new \RuntimeException(sprintf('Could not resolve template for resource "%s".', $this->metadata->getAlias()));
        }

        return $template;
    }

    /**
     * @return string|null
     */
    public function getFormType()
    {
        $form = $this->parameters->get('form');
        if (isset($form['type'])) {
            return $form['type'];
        }

        if (is_string($form)) {
            return $form;
        }

        return $this->metadata->getClass('form');
    }

    /**
     * @return array
     */
    public function getFormOptions()
    {
        $form = $this->parameters->get('form');

        return $form['options'] ?? [];
    }

    /**
     * @param string $name
     */
    public function getRouteName($name): string
    {
        $section = $this->getSection();
        $sectionPrefix = $section ? $section . '_' : '';

        return sprintf('%s_%s%s_%s', $this->metadata->getApplicationName(), $sectionPrefix, $this->metadata->getName(), $name);
    }

    /**
     * @param string $name
     *
     * @return mixed|string|null
     */
    public function getRedirectRoute($name)
    {
        $redirect = $this->parameters->get('redirect');

        if (null === $redirect) {
            return $this->getRouteName($name);
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
    public function getRedirectHash(): string
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
    public function getRedirectReferer()
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
    public function getRedirectParameters($resource = null): array
    {
        $redirect = $this->parameters->get('redirect');

        if (isset($redirect['parameters']) && $redirect['parameters'] === []) {
            return [];
        }

        if (!is_array($redirect)) {
            $redirect = ['parameters' => []];
        }

        $parameters = $redirect['parameters'] ?? [];
        $parameters = $this->addExtraRedirectParameters($parameters);

        if (null !== $resource) {
            return $this->parseResourceValues($parameters, $resource);
        }

        return $parameters;
    }

    /**
     * @param array $parameters
     */
    private function addExtraRedirectParameters($parameters): array
    {
        $vars = $this->getVars();
        $accessor = PropertyAccess::createPropertyAccessor();

        if ($accessor->isReadable($vars, '[redirect][parameters]')) {
            $extraParameters = $accessor->getValue($vars, '[redirect][parameters]');

            if (is_array($extraParameters)) {
                $parameters = array_merge($parameters, $extraParameters);
            }
        }

        return $parameters;
    }

    public function isLimited(): bool
    {
        return (bool) $this->parameters->get('limit', false);
    }

    public function getLimit(): ?int
    {
        if ($this->isLimited()) {
            return (int) $this->parameters->get('limit', 10);
        }

        return null;
    }

    public function isPaginated(): bool
    {
        $pagination = $this->parameters->get('paginate', true);

        return $pagination !== false && $pagination !== null;
    }

    public function getPaginationMaxPerPage(): int
    {
        return (int) $this->parameters->get('paginate', 10);
    }

    public function isFilterable(): bool
    {
        return (bool) $this->parameters->get('filterable', false);
    }

    /**
     * @return array
     */
    public function getCriteria(array $criteria = [])
    {
        $defaultCriteria = array_merge($this->parameters->get('criteria', []), $criteria);

        if ($this->isFilterable()) {
            return $this->getRequestParameter('criteria', $defaultCriteria);
        }

        return $defaultCriteria;
    }

    public function isSortable(): bool
    {
        return (bool) $this->parameters->get('sortable', false);
    }

    /**
     * @return array
     */
    public function getSorting(array $sorting = [])
    {
        $defaultSorting = array_merge($this->parameters->get('sorting', []), $sorting);

        if ($this->isSortable()) {
            $sorting = $this->getRequestParameter('sorting');
            foreach ($defaultSorting as $key => $value) {
                if (!isset($sorting[$key])) {
                    $sorting[$key] = $value;
                }
            }

            return $sorting;
        }

        return $defaultSorting;
    }

    /**
     * @param array $defaults
     *
     */
    public function getRequestParameter(string $parameter, $defaults = []): array
    {
        return array_replace_recursive(
            $defaults,
            RequestParameterProvider::provide($this->request, $parameter, []),
        );
    }

    /**
     * @return array|string|null
     */
    public function getRepositoryMethod()
    {
        if (!$this->parameters->has('repository')) {
            return null;
        }

        $repository = $this->parameters->get('repository');

        return is_array($repository) ? $repository['method'] : $repository;
    }

    public function getRepositoryArguments(): array
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
    public function getFactoryMethod()
    {
        if (!$this->parameters->has('factory')) {
            return null;
        }

        $factory = $this->parameters->get('factory');

        return is_array($factory) ? $factory['method'] : $factory;
    }

    public function getFactoryArguments(): array
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
    public function getFlashMessage($message): mixed
    {
        return $this->parameters->get('flash', sprintf('%s.%s.%s', $this->metadata->getApplicationName(), $this->metadata->getName(), $message));
    }

    /**
     * @return mixed|null
     */
    public function getSortablePosition(): mixed
    {
        return $this->parameters->get('sortable_position', 'position');
    }

    /**
     * @return array|null
     */
    public function getSerializationGroups(): mixed
    {
        return $this->parameters->get('serialization_groups', []);
    }

    /**
     * @return mixed|null
     */
    public function getSerializationVersion(): mixed
    {
        return $this->parameters->get('serialization_version');
    }

    /**
     * @return string|null
     */
    public function getEvent(): mixed
    {
        return $this->parameters->get('event');
    }

    public function hasPermission(): bool
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
    public function getPermission($name)
    {
        $permission = $this->parameters->get('permission');

        if (null === $permission) {
            throw new \LogicException('Current action does not require any authorization.');
        }

        if (true === $permission) {
            return sprintf('%s.%s.%s', $this->metadata->getApplicationName(), $this->metadata->getName(), $name);
        }

        return $permission;
    }

    /**
     * @return bool
     */
    public function isHeaderRedirection()
    {
        $redirect = $this->parameters->get('redirect');

        if (!is_array($redirect) || !isset($redirect['header'])) {
            return false;
        }

        if ('xhr' === $redirect['header']) {
            return $this->getRequest()->isXmlHttpRequest();
        }

        return (bool) $redirect['header'];
    }

    /**
     * @return array
     */
    public function getVars(): mixed
    {
        return $this->parameters->get('vars', []);
    }

    /**
     * @param object $resource
     */
    private function parseResourceValues(array $parameters, $resource): array
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        if (empty($parameters)) {
            return ['id' => $accessor->getValue($resource, 'id')];
        }

        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $parameters[$key] = $this->parseResourceValues($value, $resource);
            }

            if (is_string($value) && str_starts_with($value, 'resource.')) {
                $parameters[$key] = $accessor->getValue($resource, substr($value, 9));
            }
        }

        return $parameters;
    }

    /**
     * @return bool
     */
    public function hasGrid()
    {
        return $this->parameters->has('grid');
    }

    /**
     * @return string
     *
     * @throws \LogicException
     */
    public function getGrid(): mixed
    {
        if (!$this->hasGrid()) {
            throw new \LogicException('Current action does not use grid.');
        }

        return $this->parameters->get('grid');
    }

    /**
     * @return bool
     */
    public function hasStateMachine()
    {
        return $this->parameters->has('state_machine');
    }

    /**
     * @return string|null
     */
    public function getStateMachineGraph()
    {
        $options = $this->parameters->get('state_machine');

        return $options['graph'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getStateMachineTransition()
    {
        $options = $this->parameters->get('state_machine');

        return $options['transition'] ?? null;
    }

    /**
     * @return bool
     */
    public function isCsrfProtectionEnabled(): mixed
    {
        return $this->parameters->get('csrf_protection', true);
    }
}
