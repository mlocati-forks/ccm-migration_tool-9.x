<?php

namespace PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\Element;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\PublishTargetType;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\PageType\PublishTargetTypeObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Importer\ContentType\TypeInterface;
use PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\ElementParserInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class PageTypePublishTargetType implements ElementParserInterface
{

    public function getObjectCollection(\SimpleXMLElement $element)
    {
        $collection = new PublishTargetTypeObjectCollection();
        if ($element->pagetypepublishtargettypes->type) {
            foreach($element->pagetypepublishtargettypes->type as $node) {
                $type = new PublishTargetType();
                $type->setHandle((string) $node['handle']);
                $type->setName((string) $node['name']);
                $type->setPackage((string) $node['package']);
                $collection->getTypes()->add($type);
                $type->setCollection($collection);
            }
        }
        return $collection;
    }

}