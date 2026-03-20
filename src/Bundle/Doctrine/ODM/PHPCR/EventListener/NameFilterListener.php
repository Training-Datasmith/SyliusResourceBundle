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
namespace Sylius\Bundle\Resource_Bundle\Doctrine\ODM\PHPCR\Event_Listener;

use Doctrine\ODM\PHPCR\Document_Manager_Interface;
use Sylius\Bundle\Resource_Bundle\Event\Resource_Controller_Event;
trigger_deprecation('sylius/resource-bundle', '1.3', 'The "%s" class is deprecated. Doctrine MongoDB and PHPCR support will no longer be supported in 2.0.', Name_Filter_Listener::class);
/**
 * Filter the node name field, replacing invalid characters with a substitute
 * characters.
 *
 * @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.2.2%20Local%20Names
 * @see https://github.com/phpcr/phpcr-utils/blob/master/src/PHPCR/Util/PathHelper.php#L95
 */
class Name_Filter_Listener
{
    /** @var DocumentManagerInterface */
    private $document_manager;
    /**
     * @param string $replacementCharacter
     */
    public function __construct(Document_Manager_Interface $document_manager, private $replacement_character = ' ')
    {
        $this->document_manager = $document_manager;
    }
    public function on_event(Resource_Controller_Event $event): void
    {
        $document = $event->get_subject();
        $metadata = $this->document_manager->get_class_metadata($document::class);
        if (null === $name_field = $metadata->nodename) {
            throw new \RuntimeException(sprintf('In order to use the node name filter on "%s" it is necessary to map a field as the "nodename"', $document::class));
        }
        $name = $metadata->get_field_value($document, $name_field);
        $name = preg_replace('/\/|:|\[|\]|\||\*/', $this->replacement_character, (string) $name);
        $metadata->set_field_value($document, $name_field, $name);
    }
}