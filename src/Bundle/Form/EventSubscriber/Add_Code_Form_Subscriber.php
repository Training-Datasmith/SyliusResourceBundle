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
namespace Sylius\Bundle\Resource_Bundle\Form\Event_Subscriber;

use Sylius\Resource\Exception\Unexpected_Type_Exception;
use Sylius\Resource\Model\Code_Aware_Interface;
use Symfony\Component\Event_Dispatcher\Event_Subscriber_Interface;
use Symfony\Component\Form\Extension\Core\Type\Text_Type;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
final readonly class Add_Code_Form_Subscriber implements Event_Subscriber_Interface
{
    private string $type;
    public function __construct(?string $type = null, private array $options = [])
    {
        $this->type = $type ?? Text_Type::class;
    }
    public static function get_subscribed_events(): array
    {
        return [Form_Events::PRE_SET_DATA => 'preSetData'];
    }
    public function pre_set_data(Form_Event $event): void
    {
        $resource = $event->get_data();
        $disabled = false;
        if ($resource instanceof Code_Aware_Interface) {
            $disabled = null !== $resource->get_code();
        } elseif (null !== $resource) {
            throw new Unexpected_Type_Exception($resource, Code_Aware_Interface::class);
        }
        $form = $event->get_form();
        $form->add('code', $this->type, array_merge(['label' => 'sylius.ui.code'], $this->options, ['disabled' => $disabled]));
    }
}