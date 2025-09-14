<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\CIF\Block;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\ImportedBlockValue;

defined('C5_EXECUTE') or die("Access Denied.");

class Importer extends AbstractImporter
{
    public function parse(\SimpleXMLElement $node)
    {
        $value = new ImportedBlockValue();

        return $value
            ->setOriginal((string) $node->asXML())
            ->setValue((string) $node->asXML())
        ;
    }
}
