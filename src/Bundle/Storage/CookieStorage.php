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

use Sylius\Resource\Storage\Storage_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Http_Foundation\Cookie;
use Symfony\Component\Http_Foundation\Parameter_Bag;
use Symfony\Component\Http_Kernel\Event\Request_Event;
use Symfony\Component\Http_Kernel\Event\Response_Event;
use Symfony\Component\Http_Kernel\Kernel_Events;
use Webmozart\Assert\Assert;
final class Cookie_Storage implements Storage_Interface, Event_Subscriber_Interface
{
    private Parameter_Bag $request_cookies;
    private Parameter_Bag $response_cookies;
    public function __construct()
    {
        $this->request_cookies = new Parameter_Bag();
        $this->response_cookies = new Parameter_Bag();
    }
    public static function get_subscribed_events(): array
    {
        return [Kernel_Events::REQUEST => [['onKernelRequest', 1024]], Kernel_Events::RESPONSE => [['onKernelResponse', -1024]]];
    }
    public function on_kernel_request(Request_Event $event): void
    {
        if (!$event->is_main_request()) {
            return;
        }
        $this->request_cookies = new Parameter_Bag($event->get_request()->cookies->all());
        $this->response_cookies = new Parameter_Bag();
    }
    public function on_kernel_response(Response_Event $event): void
    {
        if (!$event->is_main_request()) {
            return;
        }
        $response = $event->get_response();
        /** @var string|null $value */
        foreach ($this->response_cookies as $name => $value) {
            Assert::null_or_string($value);
            $response->headers->set_cookie(new Cookie($name, $value));
        }
        $this->request_cookies = new Parameter_Bag();
        $this->response_cookies = new Parameter_Bag();
    }
    public function has(string $name): bool
    {
        return !in_array($this->get($name), ['', null], true);
    }
    public function get(string $name, $default = null)
    {
        return $this->response_cookies->get($name, $this->request_cookies->get($name, $default));
    }
    public function set(string $name, $value): void
    {
        $this->response_cookies->set($name, $value);
    }
    public function remove(string $name): void
    {
        $this->set($name, null);
    }
    public function all(): array
    {
        return array_merge($this->response_cookies->all(), $this->request_cookies->all());
    }
}