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
namespace Sylius\Resource\Twig\Context\Factory;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Operation;
use Symfony\Component\Form\Form_Interface;
/**
 * @experimental
 */
final readonly class Request_Context_Factory implements Context_Factory_Interface
{
    public function __construct(private Context_Factory_Interface $decorated)
    {
    }
    public function create(mixed $data, Operation $operation, Context $context): array
    {
        $twig_context = $this->decorated->create($data, $operation, $context);
        $request = $context->get(Request_Option::class)?->request();
        if (null === $request) {
            return $twig_context;
        }
        /** @var FormInterface|null $form */
        $form = $request->attributes->get('form');
        if (null === $form) {
            return $twig_context;
        }
        return array_merge($twig_context, ['form' => $form->create_view()]);
    }
}