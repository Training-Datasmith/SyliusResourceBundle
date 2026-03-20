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

use Symfony\Component\Security\Core\Authentication\Token\Null_Token;
use Symfony\Component\Security\Core\Authentication\Token\Storage\Token_Storage_Interface;
/**
 * @experimental
 */
final readonly class Token_Variables implements Variables_Interface
{
    public function __construct(private ?Token_Storage_Interface $token_storage = null)
    {
    }
    public function get_variables(): array
    {
        if (null === $this->token_storage) {
            throw new \LogicException('The "symfony/security-bundle" must be installed and configured to use the "token" & "user" attribute. Try running "composer require symfony/security-bundle"');
        }
        if (null === $token = $this->token_storage->get_token()) {
            $token = new Null_Token();
        }
        return ['token' => $token, 'user' => $token->get_user()];
    }
}