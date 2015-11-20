<?php

namespace PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\Block;

use Concrete\Core\Support\Manager as CoreManager;
use PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\Block\Importer;
use PortlandLabs\Concrete5\MigrationTool\Importer\Parser\CIF\Block\StandardImporter;

defined('C5_EXECUTE') or die("Access Denied.");

class Manager extends CoreManager
{

    public function driver($driver = null)
    {
        $method = 'create'.camelcase($driver).'Driver';
        // If a custom driver is not registered, we use unmapped
        if ($driver && !isset($this->customCreators[$driver]) && !method_exists($this, $method)) {
            return $this->createStandardDriver();
        }
        return parent::driver($driver);
    }

    public function getDefaultDriver()
    {
        return 'unmapped';
    }

    public function createUnmappedDriver()
    {
        return new Importer();
    }

    public function createStandardDriver()
    {
        return new StandardImporter();
    }

    public function createCoreAreaLayoutDriver()
    {
        return new AreaLayoutImporter();
    }

}
