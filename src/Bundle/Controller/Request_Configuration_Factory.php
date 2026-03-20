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

use Sylius\Resource\Metadata\Metadata_Interface;
use Symfony\Component\Http_Foundation\Request;
final readonly class Request_Configuration_Factory implements Request_Configuration_Factory_Interface
{
    private const API_VERSION_HEADER = 'Accept';
    private const API_GROUPS_HEADER = 'Accept';
    private const API_VERSION_REGEXP = '/(v|version)=(?P<version>[0-9\.]+)/i';
    private const API_GROUPS_REGEXP = '/(g|groups)=(?P<groups>[a-z,_\s]+)/i';
    /**
     * @psalm-param class-string<RequestConfiguration> $configurationClass
     */
    public function __construct(
        private Parameters_Parser_Interface $parameters_parser,
        /**
         * @psalm-var class-string<RequestConfiguration>
         */
        private string $configuration_class,
        private array $default_parameters = []
    )
    {
    }
    public function create(Metadata_Interface $metadata, Request $request): Request_Configuration
    {
        $parameters = array_merge($this->default_parameters, $this->parse_api_parameters($request));
        $parameters = $this->parameters_parser->parse_request_values($parameters, $request);
        /** @psalm-suppress UnsafeInstantiation */
        return new $this->configuration_class($metadata, $request, new Parameters($parameters));
    }
    /**
     * @throws \InvalidArgumentException
     */
    private function parse_api_parameters(Request $request): array
    {
        $parameters = $request->attributes->get('_sylius', []);
        /** @var string[] $apiVersionHeaders */
        $api_version_headers = $request->headers->all(self::API_VERSION_HEADER);
        foreach ($api_version_headers as $api_version_header) {
            if (preg_match(self::API_VERSION_REGEXP, $api_version_header, $matches)) {
                $parameters['serialization_version'] = $matches['version'];
            }
        }
        $allowed_serialization_groups = array_merge($parameters['allowed_serialization_groups'] ?? [], $parameters['serialization_groups'] ?? []);
        /** @var string[] $apiGroupsHeaders */
        $api_groups_headers = $request->headers->all(self::API_GROUPS_HEADER);
        foreach ($api_groups_headers as $api_groups_header) {
            if (preg_match(self::API_GROUPS_REGEXP, $api_groups_header, $matches)) {
                $parameters['serialization_groups'] = array_intersect($allowed_serialization_groups, array_map(trim(...), explode(',', $matches['groups'])));
            }
        }
        return $parameters;
    }
}