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
namespace Sylius\Bundle\Resource_Bundle\Controller;

use Doctrine\Persistence\Object_Manager;
use FOS\Rest_Bundle\View\View;
use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
use Sylius\Resource\Doctrine\Persistence\Repository_Interface;
use Sylius\Resource\Exception\Delete_Handling_Exception;
use Sylius\Resource\Exception\LogicException;
use Sylius\Resource\Exception\Update_Handling_Exception;
use Sylius\Resource\Factory\Factory_Interface;
use Sylius\Resource\Metadata\Metadata_Interface;
use Sylius\Resource\Model\Resource_Interface;
use Sylius\Resource\Resource_Actions;
use Symfony\Component\Dependency_Injection\Container_Interface;
use Symfony\Component\Http_Foundation\Request;
use Symfony\Component\Http_Foundation\Response;
use Symfony\Component\Http_Kernel\Exception\Bad_Request_Http_Exception;
use Symfony\Component\Http_Kernel\Exception\Http_Exception;
use Symfony\Component\Http_Kernel\Exception\Not_Found_Http_Exception;
use Symfony\Component\Security\Core\Exception\Access_Denied_Exception;
class Resource_Controller
{
    use Controller_Trait;
    use Container_Aware_Trait;
    use Bc_Layer_Request_Trait;
    protected Object_Manager $manager;
    public function __construct(protected Metadata_Interface $metadata, protected Request_Configuration_Factory_Interface $request_configuration_factory, protected ?View_Handler_Interface $view_handler, protected Repository_Interface $repository, protected Factory_Interface $factory, protected New_Resource_Factory_Interface $new_resource_factory, Object_Manager $manager, protected Single_Resource_Provider_Interface $single_resource_provider, protected Resources_Collection_Provider_Interface $resources_collection_provider, protected Resource_Form_Factory_Interface $resource_form_factory, protected Redirect_Handler_Interface $redirect_handler, protected Flash_Helper_Interface $flash_helper, protected Authorization_Checker_Interface $authorization_checker, protected Event_Dispatcher_Interface $event_dispatcher, protected ?State_Machine_Interface $state_machine, protected Resource_Update_Handler_Interface $resource_update_handler, protected Resource_Delete_Handler_Interface $resource_delete_handler)
    {
        $this->manager = $manager;
    }
    public function show_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::SHOW);
        $resource = $this->find_or404($configuration);
        $event = $this->event_dispatcher->dispatch(Resource_Actions::SHOW, $configuration, $resource);
        $event_response = $event->get_response();
        if (null !== $event_response) {
            return $event_response;
        }
        if ($configuration->is_html_request()) {
            return $this->render($configuration->get_template(Resource_Actions::SHOW . '.html'), ['configuration' => $configuration, 'metadata' => $this->metadata, 'resource' => $resource, $this->metadata->get_name() => $resource]);
        }
        return $this->create_rest_view($configuration, $resource);
    }
    public function index_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::INDEX);
        $resources = $this->resources_collection_provider->get($configuration, $this->repository);
        $event = $this->event_dispatcher->dispatch_multiple(Resource_Actions::INDEX, $configuration, $resources);
        $event_response = $event->get_response();
        if (null !== $event_response) {
            return $event_response;
        }
        if ($configuration->is_html_request()) {
            return $this->render($configuration->get_template(Resource_Actions::INDEX . '.html'), ['configuration' => $configuration, 'metadata' => $this->metadata, 'resources' => $resources, $this->metadata->get_plural_name() => $resources]);
        }
        return $this->create_rest_view($configuration, $resources);
    }
    public function create_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::CREATE);
        $new_resource = $this->new_resource_factory->create($configuration, $this->factory);
        $form = $this->resource_form_factory->create($configuration, $new_resource);
        $form->handle_request($request);
        if ($request->is_method('POST') && $form->is_submitted() && $form->is_valid()) {
            $new_resource = $form->get_data();
            $event = $this->event_dispatcher->dispatch_pre_event(Resource_Actions::CREATE, $configuration, $new_resource);
            if ($event->is_stopped() && !$configuration->is_html_request()) {
                throw new Http_Exception($event->get_error_code(), $event->get_message());
            }
            if ($event->is_stopped()) {
                $this->flash_helper->add_flash_from_event($configuration, $event);
                $event_response = $event->get_response();
                if (null !== $event_response) {
                    return $event_response;
                }
                return $this->redirect_handler->redirect_to_index($configuration, $new_resource);
            }
            if ($configuration->has_state_machine()) {
                $state_machine = $this->get_state_machine();
                $state_machine->apply($configuration, $new_resource);
            }
            $this->repository->add($new_resource);
            if ($configuration->is_html_request()) {
                $this->flash_helper->add_success_flash($configuration, Resource_Actions::CREATE, $new_resource);
            }
            $post_event = $this->event_dispatcher->dispatch_post_event(Resource_Actions::CREATE, $configuration, $new_resource);
            if (!$configuration->is_html_request()) {
                return $this->create_rest_view($configuration, $new_resource, Response::HTTP_CREATED);
            }
            $post_event_response = $post_event->get_response();
            if (null !== $post_event_response) {
                return $post_event_response;
            }
            return $this->redirect_handler->redirect_to_resource($configuration, $new_resource);
        }
        if ($request->is_method('POST') && $form->is_submitted() && !$form->is_valid()) {
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }
        if (!$configuration->is_html_request()) {
            return $this->create_rest_view($configuration, $form, Response::HTTP_BAD_REQUEST);
        }
        $initialize_event = $this->event_dispatcher->dispatch_initialize_event(Resource_Actions::CREATE, $configuration, $new_resource);
        $initialize_event_response = $initialize_event->get_response();
        if (null !== $initialize_event_response) {
            return $initialize_event_response;
        }
        return $this->render($configuration->get_template(Resource_Actions::CREATE . '.html'), ['configuration' => $configuration, 'metadata' => $this->metadata, 'resource' => $new_resource, $this->metadata->get_name() => $new_resource, 'form' => $form->create_view()], null, $response_code ?? Response::HTTP_OK);
    }
    public function update_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::UPDATE);
        $resource = $this->find_or404($configuration);
        $form = $this->resource_form_factory->create($configuration, $resource);
        $form->handle_request($request);
        if (in_array($request->get_method(), ['POST', 'PUT', 'PATCH'], true) && $form->is_submitted() && $form->is_valid()) {
            $resource = $form->get_data();
            /** @var ResourceControllerEvent $event */
            $event = $this->event_dispatcher->dispatch_pre_event(Resource_Actions::UPDATE, $configuration, $resource);
            if ($event->is_stopped() && !$configuration->is_html_request()) {
                throw new Http_Exception($event->get_error_code(), $event->get_message());
            }
            if ($event->is_stopped()) {
                $this->flash_helper->add_flash_from_event($configuration, $event);
                $event_response = $event->get_response();
                if (null !== $event_response) {
                    return $event_response;
                }
                return $this->redirect_handler->redirect_to_resource($configuration, $resource);
            }
            try {
                $this->resource_update_handler->handle($resource, $configuration, $this->manager);
            } catch (Update_Handling_Exception $exception) {
                if (!$configuration->is_html_request()) {
                    return $this->create_rest_view($configuration, $form, $exception->get_api_response_code());
                }
                $this->flash_helper->add_error_flash($configuration, $exception->get_flash());
                return $this->redirect_handler->redirect_to_referer($configuration);
            }
            if ($configuration->is_html_request()) {
                $this->flash_helper->add_success_flash($configuration, Resource_Actions::UPDATE, $resource);
            }
            $post_event = $this->event_dispatcher->dispatch_post_event(Resource_Actions::UPDATE, $configuration, $resource);
            if (!$configuration->is_html_request()) {
                if ($configuration->get_parameters()->get('return_content', false)) {
                    return $this->create_rest_view($configuration, $resource, Response::HTTP_OK);
                }
                return $this->create_rest_view($configuration, null, Response::HTTP_NO_CONTENT);
            }
            $post_event_response = $post_event->get_response();
            if (null !== $post_event_response) {
                return $post_event_response;
            }
            return $this->redirect_handler->redirect_to_resource($configuration, $resource);
        }
        if (in_array($request->get_method(), ['POST', 'PUT', 'PATCH'], true) && $form->is_submitted() && !$form->is_valid()) {
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }
        if (!$configuration->is_html_request()) {
            return $this->create_rest_view($configuration, $form, Response::HTTP_BAD_REQUEST);
        }
        $initialize_event = $this->event_dispatcher->dispatch_initialize_event(Resource_Actions::UPDATE, $configuration, $resource);
        $initialize_event_response = $initialize_event->get_response();
        if (null !== $initialize_event_response) {
            return $initialize_event_response;
        }
        return $this->render($configuration->get_template(Resource_Actions::UPDATE . '.html'), ['configuration' => $configuration, 'metadata' => $this->metadata, 'resource' => $resource, $this->metadata->get_name() => $resource, 'form' => $form->create_view()], null, $response_code ?? Response::HTTP_OK);
    }
    public function delete_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::DELETE);
        $resource = $this->find_or404($configuration);
        if ($configuration->is_csrf_protection_enabled() && !$this->is_csrf_token_valid((string) $resource->get_id(), (string) $request->request->get('_csrf_token'))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }
        $event = $this->event_dispatcher->dispatch_pre_event(Resource_Actions::DELETE, $configuration, $resource);
        if ($event->is_stopped() && !$configuration->is_html_request()) {
            throw new Http_Exception($event->get_error_code(), $event->get_message());
        }
        if ($event->is_stopped()) {
            $this->flash_helper->add_flash_from_event($configuration, $event);
            $event_response = $event->get_response();
            if (null !== $event_response) {
                return $event_response;
            }
            return $this->redirect_handler->redirect_to_index($configuration, $resource);
        }
        try {
            $this->resource_delete_handler->handle($resource, $this->repository);
        } catch (Delete_Handling_Exception $exception) {
            if (!$configuration->is_html_request()) {
                return $this->create_rest_view($configuration, null, $exception->get_api_response_code());
            }
            $this->flash_helper->add_error_flash($configuration, $exception->get_flash());
            return $this->redirect_handler->redirect_to_referer($configuration);
        }
        if ($configuration->is_html_request()) {
            $this->flash_helper->add_success_flash($configuration, Resource_Actions::DELETE, $resource);
        }
        $post_event = $this->event_dispatcher->dispatch_post_event(Resource_Actions::DELETE, $configuration, $resource);
        if (!$configuration->is_html_request()) {
            return $this->create_rest_view($configuration, null, Response::HTTP_NO_CONTENT);
        }
        $post_event_response = $post_event->get_response();
        if (null !== $post_event_response) {
            return $post_event_response;
        }
        return $this->redirect_handler->redirect_to_index($configuration, $resource);
    }
    public function bulk_delete_action(Request $request): Response
    {
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::BULK_DELETE);
        $resources = $this->resources_collection_provider->get($configuration, $this->repository);
        if ($configuration->is_csrf_protection_enabled() && !$this->is_csrf_token_valid(Resource_Actions::BULK_DELETE, (string) $request->request->get('_csrf_token'))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid csrf token.');
        }
        $this->event_dispatcher->dispatch_multiple(Resource_Actions::BULK_DELETE, $configuration, $resources);
        foreach ($resources as $resource) {
            $event = $this->event_dispatcher->dispatch_pre_event(Resource_Actions::DELETE, $configuration, $resource);
            if ($event->is_stopped() && !$configuration->is_html_request()) {
                throw new Http_Exception($event->get_error_code(), $event->get_message());
            }
            if ($event->is_stopped()) {
                $this->flash_helper->add_flash_from_event($configuration, $event);
                $event_response = $event->get_response();
                if (null !== $event_response) {
                    return $event_response;
                }
                return $this->redirect_handler->redirect_to_index($configuration, $resource);
            }
            try {
                $this->resource_delete_handler->handle($resource, $this->repository);
            } catch (Delete_Handling_Exception $exception) {
                if (!$configuration->is_html_request()) {
                    return $this->create_rest_view($configuration, null, $exception->get_api_response_code());
                }
                $this->flash_helper->add_error_flash($configuration, $exception->get_flash());
                return $this->redirect_handler->redirect_to_referer($configuration);
            }
            $post_event = $this->event_dispatcher->dispatch_post_event(Resource_Actions::DELETE, $configuration, $resource);
        }
        if (!$configuration->is_html_request()) {
            return $this->create_rest_view($configuration, null, Response::HTTP_NO_CONTENT);
        }
        $this->flash_helper->add_success_flash($configuration, Resource_Actions::BULK_DELETE);
        if (isset($post_event)) {
            $post_event_response = $post_event->get_response();
            if (null !== $post_event_response) {
                return $post_event_response;
            }
        }
        return $this->redirect_handler->redirect_to_index($configuration);
    }
    public function apply_state_machine_transition_action(Request $request): Response
    {
        $state_machine = $this->get_state_machine();
        $configuration = $this->request_configuration_factory->create($this->metadata, $request);
        $this->is_granted_or403($configuration, Resource_Actions::UPDATE);
        $resource = $this->find_or404($configuration);
        if ($configuration->is_csrf_protection_enabled() && !$this->is_csrf_token_valid((string) $resource->get_id(), $this->get_from_request($request, '_csrf_token'))) {
            throw new Http_Exception(Response::HTTP_FORBIDDEN, 'Invalid CSRF token.');
        }
        $event = $this->event_dispatcher->dispatch_pre_event(Resource_Actions::UPDATE, $configuration, $resource);
        if ($event->is_stopped() && !$configuration->is_html_request()) {
            throw new Http_Exception($event->get_error_code(), $event->get_message());
        }
        if ($event->is_stopped()) {
            $this->flash_helper->add_flash_from_event($configuration, $event);
            $event_response = $event->get_response();
            if (null !== $event_response) {
                return $event_response;
            }
            return $this->redirect_handler->redirect_to_resource($configuration, $resource);
        }
        if (!$state_machine->can($configuration, $resource)) {
            throw new Bad_Request_Http_Exception();
        }
        try {
            $this->resource_update_handler->handle($resource, $configuration, $this->manager);
        } catch (Update_Handling_Exception $exception) {
            if (!$configuration->is_html_request()) {
                return $this->create_rest_view($configuration, $resource, $exception->get_api_response_code());
            }
            $this->flash_helper->add_error_flash($configuration, $exception->get_flash());
            return $this->redirect_handler->redirect_to_referer($configuration);
        }
        if ($configuration->is_html_request()) {
            $this->flash_helper->add_success_flash($configuration, Resource_Actions::UPDATE, $resource);
        }
        $post_event = $this->event_dispatcher->dispatch_post_event(Resource_Actions::UPDATE, $configuration, $resource);
        if (!$configuration->is_html_request()) {
            if ($configuration->get_parameters()->get('return_content', true)) {
                return $this->create_rest_view($configuration, $resource, Response::HTTP_OK);
            }
            return $this->create_rest_view($configuration, null, Response::HTTP_NO_CONTENT);
        }
        $post_event_response = $post_event->get_response();
        if (null !== $post_event_response) {
            return $post_event_response;
        }
        return $this->redirect_handler->redirect_to_resource($configuration, $resource);
    }
    /**
     * @return mixed
     */
    protected function get_parameter(string $name)
    {
        if (!$this->container instanceof Container_Interface) {
            throw new \RuntimeException(sprintf('Container passed to "%s" has to implements "%s".', self::class, Container_Interface::class));
        }
        return $this->container->get_parameter($name);
    }
    /**
     * @throws AccessDeniedException
     */
    protected function is_granted_or403(Request_Configuration $configuration, string $permission): void
    {
        if (!$configuration->has_permission()) {
            return;
        }
        $permission = $configuration->get_permission($permission);
        if (!$this->authorization_checker->is_granted($configuration, $permission)) {
            throw new Access_Denied_Exception();
        }
    }
    /**
     * @throws NotFoundHttpException
     */
    protected function find_or404(Request_Configuration $configuration): Resource_Interface
    {
        if (null === $resource = $this->single_resource_provider->get($configuration, $this->repository)) {
            throw new Not_Found_Http_Exception(sprintf('The "%s" has not been found', $this->metadata->get_humanized_name()));
        }
        return $resource;
    }
    /**
     * @param mixed $data
     */
    protected function create_rest_view(Request_Configuration $configuration, $data, ?int $status_code = null): Response
    {
        if (!class_exists(View::class) || null === $this->view_handler) {
            throw new LogicException('You can not use the "non-html" request if FriendsOfSymfony Rest Bundle is not available. Try running "composer require friendsofsymfony/rest-bundle".');
        }
        $view = View::create($data, $status_code);
        return $this->view_handler->handle($configuration, $view);
    }
    protected function get_state_machine(): State_Machine_Interface
    {
        if (null === $this->state_machine) {
            throw new LogicException('You can not use the "state-machine" if Winzou State Machine Bundle is not available. Try running "composer require winzou/state-machine-bundle".');
        }
        return $this->state_machine;
    }
}