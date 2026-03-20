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
namespace Sylius\Resource\Symfony\Response;

/**
 * @experimental
 */
interface Headers_Initiator_Interface
{
    public function initialize_headers(string $mime_type): array;
}