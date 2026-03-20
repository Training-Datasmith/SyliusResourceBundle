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
namespace Sylius\Resource\Symfony\Event_Dispatcher;

use Symfony\Component\Event_Dispatcher\Generic_Event as BaseGenericEvent;
use Symfony\Component\Http_Foundation\Response;
class Generic_Event extends Base_Generic_Event
{
    public const TYPE_ERROR = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';
    private string $message_type = '';
    private string $message = '';
    private array $message_parameters = [];
    private int $error_code = 500;
    private ?Response $response = null;
    /**
     * @psalm-suppress MissingReturnType
     */
    public function stop(string $message, string $type = self::TYPE_ERROR, array $parameters = [], int $error_code = 500): void
    {
        $this->message_type = $type;
        $this->message = $message;
        $this->message_parameters = $parameters;
        $this->error_code = $error_code;
        $this->stop_propagation();
    }
    public function is_stopped(): bool
    {
        return $this->is_propagation_stopped();
    }
    public function get_message_type(): string
    {
        return $this->message_type;
    }
    /**
     * @param string $messageType Should be one of ResourceEvent's TYPE constants
     */
    public function set_message_type(string $message_type): void
    {
        $this->message_type = $message_type;
    }
    public function get_message(): string
    {
        return $this->message;
    }
    public function set_message(string $message): void
    {
        $this->message = $message;
    }
    public function get_message_parameters(): array
    {
        return $this->message_parameters;
    }
    public function set_message_parameters(array $message_parameters): void
    {
        $this->message_parameters = $message_parameters;
    }
    public function get_error_code(): int
    {
        return $this->error_code;
    }
    public function set_error_code(int $error_code): void
    {
        $this->error_code = $error_code;
    }
    public function set_response(Response $response): void
    {
        $this->response = $response;
    }
    public function has_response(): bool
    {
        return null !== $this->response;
    }
    public function get_response(): ?Response
    {
        return $this->response;
    }
}
if (!class_exists(\Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event::class, false)) {
    class_alias(Generic_Event::class, \Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event::class);
}