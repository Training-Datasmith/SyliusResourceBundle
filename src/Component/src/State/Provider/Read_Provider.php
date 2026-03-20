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
namespace Sylius\Resource\State\Provider;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Provider_Interface;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
/**
 * @experimental
 */
final readonly class Read_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $provider)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $request = $context->get(Request_Option::class)?->request();
        if ($operation instanceof Create_Operation_Interface || !($operation->can_read() ?? true)) {
            return null;
        }
        $data = $this->provider->provide($operation, $context);
        if (null === $data) {
            throw new Not_Found_Http_Exception('Resource has not been found.');
        }
        $request?->attributes->set('data', $data);
        return $data;
    }
}