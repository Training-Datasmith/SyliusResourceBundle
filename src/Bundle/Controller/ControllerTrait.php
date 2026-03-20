<?php

declare (strict_types=1);
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Doctrine\Persistence\Manager_Registry;
use Psr\Container\Container_Interface;
use Psr\Link\Link_Interface;
use Symfony\Component\Form\Extension\Core\Type\Form_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Interface;
use Symfony\Component\Http_Foundation\Binary_File_Response;
use Symfony\Component\Http_Foundation\Exception\Session_Not_Found_Exception;
use Symfony\Component\Http_Foundation\Json_Response;
use Symfony\Component\Http_Foundation\Redirect_Response;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Foundation\Response_Header_Bag;
use Symfony\Component\Http_Foundation\Session\Flash_Bag_Aware_Session_Interface;
use Symfony\Component\Http_Foundation\Streamed_Response;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
use Symfony\Component\Http_Kernel\Http_Kernel_Interface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\Stamp_Interface;
use Symfony\Component\Routing\Generator\Url_Generator_Interface;
use Symfony\Component\Security\Core\Exception\Access_Denied_Exception;
use Symfony\Component\Security\Csrf\Csrf_Token;
use Symfony\Component\Web_Link\Event_Listener\Add_Link_Header_Listener;
use Symfony\Component\Web_Link\Generic_Link_Provider;
/**
 * Common features needed in controllers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 *
 * @property ContainerInterface $container
 */
trait Controller_Trait
{
    /**
     * Returns true if the service id is defined.
     *
     * @final
     */
    protected function has(string $id): bool
    {
        return $this->container->has($id);
    }
    /**
     * Gets a container service by its id.
     *
     * @return object The service
     *
     * @final
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }
    /**
     * Generates a URL from the given parameters.
     *
     * @see UrlGeneratorInterface
     *
     * @final
     */
    protected function generate_url(string $route, array $parameters = [], int $reference_type = Url_Generator_Interface::ABSOLUTE_PATH): string
    {
        return $this->container->get('router')->generate($route, $parameters, $reference_type);
    }
    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like Bundle\BlogBundle\Controller\PostController::indexAction)
     *
     * @final
     */
    protected function forward(string $controller, array $path = [], array $query = []): Response
    {
        $request = $this->container->get('request_stack')->get_current_request();
        $path['_controller'] = $controller;
        $sub_request = $request->duplicate($query, null, $path);
        return $this->container->get('http_kernel')->handle($sub_request, Http_Kernel_Interface::SUB_REQUEST);
    }
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @final
     */
    protected function redirect(string $url, int $status = 302): Redirect_Response
    {
        return new Redirect_Response($url, $status);
    }
    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @final
     */
    protected function redirect_to_route(string $route, array $parameters = [], int $status = 302): Redirect_Response
    {
        return $this->redirect($this->generate_url($route, $parameters), $status);
    }
    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @final
     */
    protected function json($data, int $status = 200, array $headers = [], array $context = []): Json_Response
    {
        if ($this->container->has('serializer')) {
            $json = $this->container->get('serializer')->serialize($data, 'json', array_merge(['json_encode_options' => Json_Response::DEFAULT_ENCODING_OPTIONS], $context));
            return new Json_Response($json, $status, $headers, true);
        }
        return new Json_Response($data, $status, $headers);
    }
    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     *
     * @param \SplFileInfo|string $file File object or path to file to be sent as response
     *
     * @final
     */
    protected function file($file, ?string $file_name = null, string $disposition = Response_Header_Bag::DISPOSITION_ATTACHMENT): Binary_File_Response
    {
        $response = new Binary_File_Response($file);
        $response->set_content_disposition($disposition, $file_name ?? $response->get_file()->get_filename());
        return $response;
    }
    /**
     * Adds a flash message to the current session for type.
     *
     * @throws \LogicException
     *
     * @final
     */
    protected function add_flash(string $type, $message)
    {
        try {
            $session = $this->container->get('request_stack')->get_session();
        } catch (Session_Not_Found_Exception $e) {
            throw new \LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }
        if (!$session instanceof Flash_Bag_Aware_Session_Interface) {
            trigger_deprecation('symfony/framework-bundle', '6.2', 'Calling "addFlash()" method when the session does not implement %s is deprecated.', Flash_Bag_Aware_Session_Interface::class);
        }
        $session->get_flash_bag()->add($type, $message);
    }
    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied subject.
     *
     * @throws \LogicException
     *
     * @final
     */
    protected function is_granted($attributes, $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }
        return $this->container->get('security.authorization_checker')->is_granted($attributes, $subject);
    }
    /**
     * Throws an exception unless the attributes are granted against the current authentication token and optionally
     * supplied subject.
     *
     * @throws AccessDeniedException
     *
     * @final
     */
    protected function deny_access_unless_granted($attributes, $subject = null, string $message = 'Access Denied.')
    {
        if (!$this->is_granted($attributes, $subject)) {
            $exception = $this->create_access_denied_exception($message);
            $exception->set_attributes($attributes);
            $exception->set_subject($subject);
            throw $exception;
        }
    }
    /**
     * Returns a rendered view.
     *
     * @final
     */
    protected function render_view(string $view, array $parameters = []): string
    {
        if ($this->container->has('templating')) {
            @trigger_error('Using the "templating" service is deprecated since Symfony 4.3 and will be removed in 5.0; use Twig instead.', \E_USER_DEPRECATED);
            return $this->container->get('templating')->render($view, $parameters);
        }
        if (!$this->container->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }
        return $this->container->get('twig')->render($view, $parameters);
    }
    /**
     * Renders a view.
     *
     * @final
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null, ?int $response_code = null): Response
    {
        if ($this->container->has('templating')) {
            @trigger_error('Using the "templating" service is deprecated since Symfony 4.3 and will be removed in 5.0; use Twig instead.', \E_USER_DEPRECATED);
            $content = $this->container->get('templating')->render($view, $parameters);
        } elseif ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render($view, $parameters);
        } else {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }
        if (null === $response) {
            $response = new Response();
        }
        $response->set_content($content);
        if ($response_code !== null) {
            $response->set_status_code($response_code);
        }
        return $response;
    }
    /**
     * Streams a view.
     *
     * @final
     */
    protected function stream(string $view, array $parameters = [], ?Streamed_Response $response = null): Streamed_Response
    {
        if ($this->container->has('templating')) {
            @trigger_error('Using the "templating" service is deprecated since Symfony 4.3 and will be removed in 5.0; use Twig instead.', \E_USER_DEPRECATED);
            $templating = $this->container->get('templating');
            $callback = function () use ($templating, $view, $parameters): void {
                $templating->stream($view, $parameters);
            };
        } elseif ($this->container->has('twig')) {
            $twig = $this->container->get('twig');
            $callback = function () use ($twig, $view, $parameters): void {
                $twig->display($view, $parameters);
            };
        } else {
            throw new \LogicException('You can not use the "stream" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }
        if (null === $response) {
            return new Streamed_Response($callback);
        }
        $response->set_callback($callback);
        return $response;
    }
    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @final
     */
    protected function create_not_found_exception(string $message = 'Not Found', ?\Throwable $previous = null): Not_Found_Http_Exception
    {
        return new Not_Found_Http_Exception($message, $previous);
    }
    /**
     * Returns an AccessDeniedException.
     *
     * This will result in a 403 response code. Usage example:
     *
     *     throw $this->createAccessDeniedException('Unable to access this page!');
     *
     * @throws \LogicException If the Security component is not available
     *
     * @final
     */
    protected function create_access_denied_exception(string $message = 'Access Denied.', ?\Throwable $previous = null): Access_Denied_Exception
    {
        if (!class_exists(Access_Denied_Exception::class)) {
            throw new \LogicException('You can not use the "createAccessDeniedException" method if the Security component is not available. Try running "composer require symfony/security-bundle".');
        }
        return new Access_Denied_Exception($message, $previous);
    }
    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @final
     */
    protected function create_form(string $type, $data = null, array $options = []): Form_Interface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }
    /**
     * Creates and returns a form builder instance.
     *
     * @final
     */
    protected function create_form_builder($data = null, array $options = []): Form_Builder_Interface
    {
        return $this->container->get('form.factory')->create_builder(Form_Type::class, $data, $options);
    }
    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return ManagerRegistry
     *
     * @throws \LogicException If DoctrineBundle is not available
     *
     * @final
     */
    protected function get_doctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application. Try running "composer require symfony/orm-pack".');
        }
        return $this->container->get('doctrine');
    }
    /**
     * Get a user from the Security Token Storage.
     *
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @see TokenInterface::getUser()
     * @final
     */
    protected function get_user(): ?object
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application. Try running "composer require symfony/security-bundle".');
        }
        if (null === $token = $this->container->get('security.token_storage')->get_token()) {
            return null;
        }
        if (!\is_object($user = $token->get_user())) {
            // e.g. anonymous authentication
            return null;
        }
        return $user;
    }
    /**
     * Checks the validity of a CSRF token.
     *
     * @param string      $id    The id used when generating the token
     * @param string|null $token The actual token sent with the request that should be validated
     *
     * @final
     */
    protected function is_csrf_token_valid(string $id, ?string $token): bool
    {
        if (!$this->container->has('security.csrf.token_manager')) {
            throw new \LogicException('CSRF protection is not enabled in your application. Enable it with the "csrf_protection" key in "config/packages/framework.yaml".');
        }
        return $this->container->get('security.csrf.token_manager')->is_token_valid(new Csrf_Token($id, $token));
    }
    /**
     * Dispatches a message to the bus.
     *
     * @param object|Envelope  $message The message or the message pre-wrapped in an envelope
     * @param StampInterface[] $stamps
     *
     * @final
     */
    protected function dispatch_message($message, array $stamps = []): Envelope
    {
        if (!$this->container->has('messenger.default_bus')) {
            $message = class_exists(Envelope::class) ? 'You need to define the "messenger.default_bus" configuration option.' : 'Try running "composer require symfony/messenger".';
            throw new \LogicException('The message bus is not enabled in your application. ' . $message);
        }
        return $this->container->get('messenger.default_bus')->dispatch($message, $stamps);
    }
    /**
     * Adds a Link HTTP header to the current response.
     *
     * @see https://tools.ietf.org/html/rfc5988
     *
     * @final
     */
    protected function add_link(Request $request, Link_Interface $link)
    {
        if (!class_exists(Add_Link_Header_Listener::class)) {
            throw new \LogicException('You can not use the "addLink" method if the WebLink component is not available. Try running "composer require symfony/web-link".');
        }
        if (null === $link_provider = $request->attributes->get('_links')) {
            $request->attributes->set('_links', new Generic_Link_Provider([$link]));
            return;
        }
        $request->attributes->set('_links', $link_provider->with_link($link));
    }
}