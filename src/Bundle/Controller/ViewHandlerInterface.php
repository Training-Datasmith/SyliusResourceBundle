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

use FOS\Rest_Bundle\View\View;
use Symfony\Component\Http_Foundation\Response;
interface View_Handler_Interface
{
    public function handle(Request_Configuration $request_configuration, View $view): Response;
}