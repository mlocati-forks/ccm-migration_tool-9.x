<?php

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Command;

use PortlandLabs\Concrete5\MigrationTool\Publisher\Command\Handler\CreateContentEditorSnippetsCommandHandler;

class CreateContentEditorSnippetsCommand extends PublisherCommand
{

    public static function getHandler(): string
    {
        return CreateContentEditorSnippetsCommandHandler::class;
    }



}