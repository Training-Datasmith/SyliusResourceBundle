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
namespace Sylius\Bundle\Resource_Bundle\Form\Extension\Http_Foundation;

use Symfony\Component\Form\Exception\Unexpected_Type_Exception;
use Symfony\Component\Form\Form_Error;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Form\Request_Handler_Interface;
use Symfony\Component\Form\Util\Server_Params;
use Symfony\Component\Http_Foundation\File\File;
use Symfony\Component\Http_Foundation\Request;
use Webmozart\Assert\Assert;
/**
 * Does not compare the form's method with the request's method.
 * Always submits the form, even if there are no fields sent.
 *
 * @internal
 *
 * @see \Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler
 */
final readonly class Http_Foundation_Request_Handler implements Request_Handler_Interface
{
    private Server_Params $server_params;
    public function __construct(?Server_Params $server_params = null)
    {
        $this->server_params = $server_params ?: new Server_Params();
    }
    public function handle_request(Form_Interface $form, mixed $request = null): void
    {
        if (!$request instanceof Request) {
            throw new Unexpected_Type_Exception($request, Request::class);
        }
        $name = $form->get_name();
        $method = $request->get_method();
        // For request methods that must not have a request body we fetch data
        // from the query string. Otherwise we look for data in the request body.
        if ('GET' === $method || 'HEAD' === $method || 'TRACE' === $method) {
            if ('' === $name) {
                /** @var array<string, mixed> $data */
                $data = $request->query->all();
            } else {
                // Don't submit GET requests if the form's name does not exist
                // in the request
                if (!$request->query->has($name)) {
                    return;
                }
                /** @var array<string, mixed> $data */
                $data = $request->query->all()[$name];
            }
        } else {
            // Mark the form with an error if the uploaded size was too large
            // This is done here and not in FormValidator because $_POST is
            // empty when that error occurs. Hence the form is never submitted.
            if ($this->server_params->has_post_max_size_been_exceeded()) {
                // Submit the form, but don't clear the default values
                $form->submit(null, false);
                $upload_max_size_message_callable = $form->get_config()->get_option('upload_max_size_message');
                Assert::is_callable($upload_max_size_message_callable);
                $upload_max_size_message = call_user_func($upload_max_size_message_callable);
                Assert::string($upload_max_size_message);
                $form->add_error(new Form_Error($upload_max_size_message, null, ['{{ max }}' => $this->server_params->get_normalized_ini_post_max_size()]));
                return;
            }
            if ('' === $name) {
                $params = $request->request->all();
                $files = $request->files->all();
            } elseif ($request->request->has($name) || $request->files->has($name)) {
                /** @psalm-var array|null $default */
                $default = $form->get_config()->get_compound() ? [] : null;
                $params = $request->request->all()[$name] ?? $default;
                $files = $request->files->get($name, $default);
            } else {
                // Don't submit the form if it is not present in the request
                return;
            }
            if (is_array($params) && is_array($files)) {
                $data = array_replace_recursive($params, $files);
            } else {
                /** @var array<string, mixed> $data */
                $data = $params ?: $files;
            }
        }
        $form->submit($data, 'PATCH' !== $method);
    }
    public function is_file_upload(mixed $data): bool
    {
        return $data instanceof File;
    }
}