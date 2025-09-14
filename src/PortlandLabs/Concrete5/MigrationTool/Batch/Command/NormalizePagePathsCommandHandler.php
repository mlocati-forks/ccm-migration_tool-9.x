<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\Item\Item;
use PortlandLabs\Concrete5\MigrationTool\Batch\ContentMapper\PresetManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\ImportedBlockValue;
use Doctrine\ORM\EntityManagerInterface;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use Generator;

class NormalizePagePathsCommandHandler
{

    public function __invoke(NormalizePagePathsCommand $command)
    {
        $em = app(EntityManagerInterface::class);
        $batch = $em->find(Batch::class, $command->getBatchId());
        $pages = $batch->getPages();
        $pagesToNormalize = [];
        foreach ($pages as $page) {
            if ($batch->isPublishToSitemap() || !$page->canNormalizePath()) {
                $page->setBatchPath($page->getOriginalPath());
            } else {
                $pagesToNormalize[] = $page;
            }
        }
        $map = $this->normalizePagePaths($pagesToNormalize);
        $this->applyMapToPageLinks($pages, $batch, $map);
        $em->flush();
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     */
    private function normalizePagePaths(array $pagesToNormalize): array
    {
        $map = [];
        $commonPrefix = $this->calculateCommonPathPrefix($pagesToNormalize);
        foreach ($pagesToNormalize as $page) {
            $originalPath = '/' . ltrim($page->getOriginalPath() ?? '', '/');
            $newPath = substr($originalPath, strlen($commonPrefix) - 1);
            $page->setBatchPath($newPath);
            $map[$originalPath] = $newPath;
        }

        return $map;
    }
    
    private function applyMapToPageLinks(array $pages, Batch $batch, array $map): void
    {
        $pathPrefix = $batch->isPublishToSitemap() ? '' : "/!import_batches/{$batch->getID()}";
        foreach ($this->listImportedBlockValues($pages) as $importedBlockValue) {
            $value = $importedBlockValue->getOriginalValue();
            if ($value) {
                $value = preg_replace_callback(
                    '/\{ccm:export:page:(?<path>.*?)\}/',
                    static function (array $matches) use (&$map, $pathPrefix): string {
                        $path = '/' . ltrim($matches['path'], '/');
                        if (isset($map[$path])) {
                            $path = $pathPrefix . $map[$path];
                        }
                        return "{ccm:export:page:{$path}}";
                    },
                    $value
                );
            }
            $importedBlockValue->setValue($value);
        }
    }

    /**
     * Calculate the common prefix of all pages
     *
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     *
     * @example if we have 1 page at '/path/to/my/page', you'll get '/path/to/my/'
     * @example if we have 2 pages at '/path/to/my/page' and '/path/to/another/page', you'll get '/path/to/'
     * @example if we have 2 pages at '/first/page' and '/second/page', you'll get '/'
     */
    private function calculateCommonPathPrefix(array $pages): string
    {
        if ($pages === []) {
            return '/';
        }
        $commonSlugs = null;
        foreach ($pages as $page) {
            $pageSlugs = preg_split('{/}', $page->getOriginalPath() ?? '', -1, PREG_SPLIT_NO_EMPTY);
            array_pop($pageSlugs);
            if ($commonSlugs === null) {
                $commonSlugs = $pageSlugs;
            } else {
                $newCommonSlugs = [];
                foreach ($commonSlugs as $index => $slug) {
                    if (!isset($pageSlugs[$index]) || $pageSlugs[$index] !== $slug) {
                        break;
                    }
                    $newCommonSlugs[] = $slug;
                }
                $commonSlugs = $newCommonSlugs;
            }
            if ($commonSlugs === []) {
                break;
            }
        }
        if ($commonSlugs === []) {
            return '/';
        }

        return '/' . implode('/', $commonSlugs) . '/';
    }

    /**
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     *
     * @return \Generator|\PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\ImportedBlockValue[]
     */
    private function listImportedBlockValues(array $pages): Generator
    {
        foreach ($pages as $page) {
            foreach ($page->getAreas() as $area) {
                foreach ($area->getBlocks() as $block) {
                    $value = $block->getBlockValue();
                    if ($value instanceof ImportedBlockValue) {
                        yield $value;
                    }
                }
            }
        }
    }
}
