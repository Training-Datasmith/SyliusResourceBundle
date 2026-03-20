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
namespace Sylius\Bundle\Resource_Bundle\Storage;

use Sylius\Resource\Exception\Storage_Unavailable_Exception;
use Sylius\Resource\Storage\Storage_Interface;
use Symfony\Component\Http_Foundation\Exception\Session_Not_Found_Exception;
use Symfony\Component\Http_Foundation\Request_Stack;
use Symfony\Component\Http_Foundation\Session\Session_Interface;
final class Session_Storage implements Storage_Interface
{
    private readonly \Symfony\Component\Http_Foundation\Request_Stack|\Symfony\Component\Http_Foundation\Session\Session_Interface $request_stack;
    /**
     * @param RequestStack|SessionInterface $requestStack
     */
    public function __construct(
        /* RequestStack */
        $request_stack
    )
    {
        /** @phpstan-ignore-next-line */
        if (!$request_stack instanceof Session_Interface && !$request_stack instanceof Request_Stack) {
            throw new \InvalidArgumentException(sprintf('The first argument of "%s" should be instance of "%s" or "%s"', __METHOD__, Session_Interface::class, Request_Stack::class));
        }
        if ($request_stack instanceof Session_Interface) {
            trigger_deprecation('sylius/resource-bundle', '1.10', 'Passing an instance of "%s" as the constructor argument for "%s" is deprecated and will not be supported in 2.0. Pass an instance of "%s" instead.', Session_Interface::class, self::class, Request_Stack::class);
        }
        $this->request_stack = $request_stack;
    }
    public function has(string $name): bool
    {
        return $this->get_session()->has($name);
    }
    public function get(string $name, $default = null)
    {
        return $this->get_session()->get($name, $default);
    }
    public function set(string $name, $value): void
    {
        $this->get_session()->set($name, $value);
    }
    public function remove(string $name): void
    {
        $this->get_session()->remove($name);
    }
    public function all(): array
    {
        return $this->get_session()->all();
    }
    /**
     * @throws StorageUnavailableException
     */
    private function get_session(): Session_Interface
    {
        try {
            if ($this->request_stack instanceof Session_Interface) {
                return $this->request_stack;
            }
            /** @phpstan-ignore-next-line */
            return $this->request_stack->get_session();
        } catch (Session_Not_Found_Exception $exception) {
            throw new Storage_Unavailable_Exception($exception->get_message(), $exception);
        }
    }
}