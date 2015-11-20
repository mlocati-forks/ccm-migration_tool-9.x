<?php

namespace PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\Element;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AttributeTypeObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockTypeSetObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\JobSetObjectCollection;
use PortlandLabs\Concrete5\MigrationTool\Importer\ContentType\TypeInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\AttributeType as CoreAttributeType;
use PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\ElementParserInterface;

defined('C5_EXECUTE') or die("Access Denied.");

class JobSet implements ElementParserInterface
{

    public function getObjectCollection(\SimpleXMLElement $element)
    {
        $collection = new JobSetObjectCollection();
        if ($element->jobsets) {
            foreach($element->jobsets->jobset as $node) {
                $set = new \PortlandLabs\Concrete5\MigrationTool\Entity\Import\JobSet();
                $set->setName((string) $node['name']);
                $set->setPackage((string) $node['package']);
                $jobs = array();
                if ($node->job) {
                    foreach($node->job as $cn) {
                        $jobs[] = (string) $cn['handle'];
                    }
                }
                $set->setJobs($jobs);
                $collection->getSets()->add($set);
                $set->setCollection($collection);
            }
        }
        return $collection;
    }

}
