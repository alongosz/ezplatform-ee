<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace App\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ParentLocationId;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause\ContentName;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;

class ChildrenController extends Controller
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function showChildrenAction(ContentView $view): ContentView
    {
        $filter = new Filter();
        $filter
            ->withCriterion(new ParentLocationId($view->getLocation()->id))
            ->withSortClause(new ContentName(Query::SORT_ASC));

        $view->setParameters(
            [
                'content_list' => $this->contentService->find($filter, []),
            ]
        );

        return $view;
    }
}
