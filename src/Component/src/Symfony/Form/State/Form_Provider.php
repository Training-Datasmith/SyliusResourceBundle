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
namespace Sylius\Resource\Symfony\Form\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Bulk_Operation_Interface;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\State\Provider_Interface;
use Sylius\Resource\Symfony\Form\Factory\Form_Factory_Interface;
use Symfony\Component\Http_Foundation\Response;
/**
 * @experimental
 */
final readonly class Form_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $decorated, private Form_Factory_Interface $form_factory)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $data = $this->decorated->provide($operation, $context);
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return $data;
        }
        /** @var string $format */
        $format = $request->get_request_format();
        if ($data instanceof Response || $operation instanceof Bulk_Operation_Interface || !($operation instanceof Create_Operation_Interface || $operation instanceof Update_Operation_Interface) || 'html' !== $format || null === $operation->get_form_type()) {
            return $data;
        }
        $form = $this->form_factory->create($operation, $context, $data);
        $form->handle_request($request);
        $request->attributes->set('form', $form);
        return $data;
    }
}