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
namespace Sylius\Bundle\Resource_Bundle\Form\Type;

use Sylius\Resource\Model\Archivable_Interface;
use Symfony\Component\Form\Abstract_Type;
use Symfony\Component\Form\Extension\Core\Type\Date_Time_Type;
use Symfony\Component\Form\Form_Builder_Interface;
use Symfony\Component\Form\Form_Event;
use Symfony\Component\Form\Form_Events;
final class Archivable_Type extends Abstract_Type
{
    public function build_form(Form_Builder_Interface $builder, array $options): void
    {
        $builder->add('archivedAt', Date_Time_Type::class)->add_event_listener(Form_Events::SUBMIT, function (Form_Event $event): void {
            /** @var ArchivableInterface $archivable */
            $archivable = $event->get_data();
            $archived_at = null;
            if (null === $archivable->get_archived_at()) {
                $archived_at = new \DateTime();
            }
            $archivable->set_archived_at($archived_at);
            $event->set_data($archivable);
        });
    }
    public function get_block_prefix(): string
    {
        return 'sylius_archivable';
    }
}