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
namespace Sylius\Resource\Metadata\Operation;

use Sylius\Resource\Metadata\Http_Operation;
use Symfony\Component\Http_Foundation\Request;
interface Http_Operation_Initiator_Interface
{
    public function initialize_operation(Request $request): ?Http_Operation;
}