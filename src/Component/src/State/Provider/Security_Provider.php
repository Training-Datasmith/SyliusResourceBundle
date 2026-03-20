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
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operation_Access_Checker_Interface;
use Sylius\Resource\State\Provider_Interface;
use Symfony\Component\Security\Core\Exception\Access_Denied_Exception;
/**
 * @experimental
 */
final readonly class Security_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $provider, private Operation_Access_Checker_Interface $operation_access_checker)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $data = $this->provider->provide($operation, $context);
        if ($this->operation_access_checker->is_granted($operation, $context, ['object' => $data])) {
            return $data;
        }
        $exception = new Access_Denied_Exception($operation->get_security_message() ?? 'Access denied.');
        $exception->set_attributes(['operation' => $operation, 'context' => $context]);
        $exception->set_subject($data);
        throw $exception;
    }
}