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

use Sylius\Resource\Model\Resource_Interface;
use Symfony\Component\Form\Form_Factory_Interface;
use Symfony\Component\Form\Form_Interface;
final readonly class Resource_Form_Factory implements Resource_Form_Factory_Interface
{
    private Form_Factory_Interface $form_factory;
    public function __construct(Form_Factory_Interface $form_factory)
    {
        $this->form_factory = $form_factory;
    }
    public function create(Request_Configuration $request_configuration, Resource_Interface $resource): Form_Interface
    {
        $form_type = (string) $request_configuration->get_form_type();
        $form_options = $request_configuration->get_form_options();
        if ($request_configuration->is_html_request()) {
            return $this->form_factory->create($form_type, $resource, $form_options);
        }
        return $this->form_factory->create_named('', $form_type, $resource, array_merge($form_options, ['csrf_protection' => false]));
    }
}