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
use Sylius\Resource\Metadata\Factory_Aware_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\Factory_Interface;
use Sylius\Resource\State\Provider_Interface;
/**
 * @experimental
 */
final readonly class Factory_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $decorated, private Factory_Interface $factory)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $data = $this->decorated->provide($operation, $context);
        $request = $context->get(Request_Option::class)?->request();
        if (!$operation instanceof Factory_Aware_Operation_Interface || !($operation->get_factory() ?? true)) {
            return $data;
        }
        $data = $this->factory->create($operation, $context);
        $request?->attributes->set('data', $data);
        return $data;
    }
}