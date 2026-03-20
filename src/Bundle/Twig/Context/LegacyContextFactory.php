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
namespace Sylius\Bundle\Resource_Bundle\Twig\Context;

use Sylius\Bundle\Resource_Bundle\Context\Option\Request_Configuration_Option;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Metadata_Option;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Twig\Context\Factory\Context_Factory_Interface;
final readonly class Legacy_Context_Factory implements Context_Factory_Interface
{
    public function __construct(private Context_Factory_Interface $decorated)
    {
    }
    public function create(mixed $data, Operation $operation, Context $context): array
    {
        $twig_context = $this->decorated->create($data, $operation, $context);
        $request_configuration = $context->get(Request_Configuration_Option::class)?->request_configuration();
        $metadata = $context->get(Metadata_Option::class)?->metadata();
        if (null !== $request_configuration) {
            $twig_context['configuration'] = $request_configuration;
        }
        if (null !== $metadata) {
            $twig_context['metadata'] = $metadata;
        }
        return $twig_context;
    }
}