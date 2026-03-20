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
namespace Sylius\Resource\Symfony\Validator\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\State\Provider_Interface;
use Sylius\Resource\Symfony\Validator\Exception\Validation_Exception;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Validator\Validator\Validator_Interface;
/**
 * @experimental
 */
final readonly class Validate_Provider implements Provider_Interface
{
    public function __construct(private Provider_Interface $decorated, private Validator_Interface $validator)
    {
    }
    public function provide(Operation $operation, Context $context): object|array|null
    {
        $data = $this->decorated->provide($operation, $context);
        $request = $context->get(Request_Option::class)?->request();
        /** @var FormInterface|null $form */
        $form = $request?->attributes->get('form');
        /** @var string $format */
        $format = $request?->get_request_format();
        if ($data instanceof Response || !($operation instanceof Create_Operation_Interface || $operation instanceof Update_Operation_Interface) || !($operation->can_validate() ?? true)) {
            return $data;
        }
        if ('html' !== $format) {
            $validation_groups = $operation->get_validation_context()['groups'] ?? null;
            $violations = $this->validator->validate(value: $data, groups: $validation_groups);
            if (0 !== \count($violations)) {
                throw new Validation_Exception($violations);
            }
            return $data;
        }
        if (null === $form || null === $request) {
            return $data;
        }
        if (!$request->is_method_safe() && $form->is_submitted() && $form->is_valid()) {
            $request->attributes->set('is_valid', true);
            /** @var array|object|null $data */
            $data = $form->get_data();
            return $data;
        }
        $request->attributes->set('is_valid', false);
        return $data;
    }
}