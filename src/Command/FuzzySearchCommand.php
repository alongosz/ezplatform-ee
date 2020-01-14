<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Command;

use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class FuzzySearchCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\SearchService */
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        parent::__construct();
        $this->searchService = $searchService;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:search:fuzzy')
            ->addOption('fuzziness', 'f', InputOption::VALUE_REQUIRED, 'Floating point number from 0 to 1')
            ->addOption('parent-location-id', 'l', InputOption::VALUE_OPTIONAL, 'Parent Location ID of a Container to search in')
            ->addArgument('search-phrase', InputArgument::REQUIRED)
        ;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $searchPhrase = $input->getArgument('search-phrase');
        $fuzziness = (float)$input->getOption('fuzziness');
        $parentLocationId = $input->hasOption('parent-location-id')
            ? $input->getOption('parent-location-id')
            : null;

        $output->writeln(
            "Searching for '<info>{$searchPhrase}</info>' with the fuzziness = <info>{$fuzziness}</info>"
        );

        $query = new Query();
        $query->query = new Query\Criterion\FullText($searchPhrase, ['fuzziness' => $fuzziness]);
        if (null !== $parentLocationId) {
            $query->filter = new Query\Criterion\ParentLocationId($parentLocationId);
        }

        $searchResults = $this->searchService->findContent($query);

        $output->writeln("Found <info>{$searchResults->totalCount}</info> results.");
        foreach ($searchResults as $searchHit) {
            /** @var \eZ\Publish\API\Repository\Values\Content\Content $content */
            $content = $searchHit->valueObject;
            $output->writeln("[<info>{$content->id}</info>] {$content->getName()}");
        }

        return $searchResults->totalCount > 0 ? 0 : 1;
    }
}
