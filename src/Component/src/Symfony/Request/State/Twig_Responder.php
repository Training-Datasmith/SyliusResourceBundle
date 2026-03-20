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
namespace Sylius\Resource\Symfony\Request\State;

use Sylius\Resource\Context\Context;
use Sylius\Resource\Context\Option\Request_Option;
use Sylius\Resource\Metadata\Create_Operation_Interface;
use Sylius\Resource\Metadata\Delete_Operation_Interface;
use Sylius\Resource\Metadata\Http_Operation;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\Metadata\Update_Operation_Interface;
use Sylius\Resource\State\Responder_Interface;
use Sylius\Resource\Symfony\Routing\Redirect_Handler_Interface;
use Sylius\Resource\Twig\Context\Factory\Context_Factory_Interface;
use Symfony\Component\Http_Foundation\Response;
use Twig\Environment;
/**
 * @experimental
 */
final readonly class Twig_Responder implements Responder_Interface
{
    public function __construct(private Redirect_Handler_Interface $redirect_handler, private Context_Factory_Interface $context_factory, private ?Environment $twig)
    {
    }
    public function respond(mixed $data, Operation $operation, Context $context): ?Response
    {
        $request = $context->get(Request_Option::class)?->request();
        if (null === $this->twig) {
            throw new \LogicException('You can not use the "Twig" if it is not available. Try running "composer require twig".');
        }
        if (null === $request) {
            return null;
        }
        $is_valid = $request->attributes->get_boolean('is_valid', true);
        if ($operation instanceof Delete_Operation_Interface && $operation instanceof Http_Operation) {
            return $this->redirect_handler->redirect_to_resource($data, $operation, $request);
        }
        if ($is_valid && !$request->is_method_safe() && $operation instanceof Http_Operation && ($operation instanceof Update_Operation_Interface || $operation instanceof Create_Operation_Interface)) {
            return $this->redirect_handler->redirect_to_resource($data, $operation, $request);
        }
        $content = $this->twig->render($operation->get_template() ?? '', $this->context_factory->create($data, $operation, $context));
        return new Response($content, $request->is_method_safe() || $is_valid ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}