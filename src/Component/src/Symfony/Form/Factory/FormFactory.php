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
namespace Sylius\Resource\Symfony\Form\Factory;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Exception\InvalidArgumentException;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Symfony\Expression_Language\Argument_Parser_Interface;
use Symfony\Component\Form\Form_Factory_Interface as SymfonyFormFactoryInterface;
use Symfony\Component\Form\Form_Interface;
/**
 * @experimental
 */
final readonly class Form_Factory implements Form_Factory_Interface
{
    public function __construct(private Symfony_Form_Factory_Interface $form_factory, private Argument_Parser_Interface $argument_parser)
    {
    }
    public function create(Operation $operation, Context $context, mixed $data = null): Form_Interface
    {
        $form_type = $operation->get_form_type();
        $form_options = $this->parse_form_options($operation->get_form_options() ?? []);
        if (null === $form_type) {
            throw new \RuntimeException(sprintf('Operation "%s" has no configured form type.', $operation->get_name() ?? ''));
        }
        $request = $context->get(Request_Option::class)?->request();
        if ('html' === $request?->get_request_format()) {
            return $this->form_factory->create($form_type, $data, $form_options);
        }
        return $this->form_factory->create_named('', $form_type, $data, array_merge($form_options, ['csrf_protection' => false]));
    }
    /**
     * @param array<string, mixed> $formOptions
     *
     * @return array<string, mixed>
     */
    private function parse_form_options(array $form_options): array
    {
        foreach ($form_options as $key => $value) {
            if (\is_array($value)) {
                $form_options[$key] = $this->parse_form_options($value);
                continue;
            }
            if (!\is_scalar($value)) {
                throw new InvalidArgumentException(sprintf('Parameter "%s" should be a scalar or an array.', $key));
            }
            if (!is_string($value)) {
                continue;
            }
            if (!str_starts_with($value, '@=')) {
                continue;
            }
            $value = substr($value, 2);
            $form_options[$key] = $this->argument_parser->parse_expression($value);
        }
        return $form_options;
    }
}