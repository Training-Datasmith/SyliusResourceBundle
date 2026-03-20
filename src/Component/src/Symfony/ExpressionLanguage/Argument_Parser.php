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
namespace Sylius\Resource\Symfony\Expression_Language;

use Symfony\Component\Expression_Language\Expression_Function_Provider_Interface;
use Symfony\Component\Expression_Language\Expression_Language;
use Webmozart\Assert\Assert;
/**
 * @experimental
 */
final readonly class Argument_Parser implements Argument_Parser_Interface
{
    public function __construct(private Expression_Language $expression_language, private Variables_Collection_Interface $variables_collection, ?iterable $providers = null)
    {
        foreach ($providers ?? [] as $provider) {
            Assert::is_instance_of($provider, Expression_Function_Provider_Interface::class);
            $this->expression_language->register_provider($provider);
        }
    }
    public function parse_expression(string $expression, array $variables = []): mixed
    {
        return $this->expression_language->evaluate($expression, array_merge($this->variables_collection->get_variables(), $variables));
    }
}