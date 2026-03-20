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
namespace Sylius\Resource\Symfony\Security;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Operation_Access_Checker_Interface;
use Symfony\Component\Expression_Language\Expression_Language;
use Symfony\Component\Security\Core\Authentication\Authentication_Trust_Resolver_Interface;
use Symfony\Component\Security\Core\Authentication\Token\Null_Token;
use Symfony\Component\Security\Core\Authentication\Token\Storage\Token_Storage_Interface;
use Symfony\Component\Security\Core\Authentication\Token\Token_Interface;
use Symfony\Component\Security\Core\Authorization\Authorization_Checker_Interface;
use Symfony\Component\Security\Core\Role\Role_Hierarchy_Interface;
final readonly class Operation_Access_Checker implements Operation_Access_Checker_Interface
{
    public function __construct(private ?Expression_Language $expression_language = null, private ?Authentication_Trust_Resolver_Interface $authentication_trust_resolver = null, private ?Role_Hierarchy_Interface $role_hierarchy = null, private ?Token_Storage_Interface $token_storage = null, private ?Authorization_Checker_Interface $authorization_checker = null)
    {
    }
    public function is_granted(Operation $operation, Context $context, array $extra_variables = []): bool
    {
        if (null === $this->token_storage || null === $this->authentication_trust_resolver) {
            throw new \LogicException('The "symfony/security" library must be installed to use the "security" attribute.');
        }
        if (null === $this->expression_language) {
            throw new \LogicException('The "symfony/expression-language" library must be installed to use the "security" attribute.');
        }
        $expression = $operation->get_security();
        if (null === $expression) {
            return true;
        }
        $token = $this->token_storage->get_token();
        if (null === $token) {
            $token = new Null_Token();
        }
        $variables = array_merge($extra_variables, $this->get_variables($token));
        return (bool) $this->expression_language->evaluate($expression, $variables);
    }
    /**
     * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Security/Core/Authorization/Voter/ExpressionVoter.php
     */
    private function get_variables(Token_Interface $token): array
    {
        $role_names = $token->get_role_names();
        if (null !== $this->role_hierarchy) {
            $role_names = $this->role_hierarchy->get_reachable_role_names($role_names);
        }
        return ['token' => $token, 'user' => $token->get_user(), 'roles' => $role_names, 'trust_resolver' => $this->authentication_trust_resolver, 'auth_checker' => $this->authorization_checker];
    }
}