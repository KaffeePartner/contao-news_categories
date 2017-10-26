<?php

namespace Codefog\NewsCategoriesBundle\EventListener;

use Codefog\NewsCategoriesBundle\Criteria;
use Codefog\NewsCategoriesBundle\SearchBuilder;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\Model\Collection;
use Contao\ModuleNewsList;
use Symfony\Component\HttpFoundation\RequestStack;

class InsertTagsListener implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SearchBuilder
     */
    private $searchBuilder;

    /**
     * InsertTagsListener constructor.
     *
     * @param RequestStack  $requestStack
     * @param SearchBuilder $searchBuilder
     */
    public function __construct(RequestStack $requestStack, SearchBuilder $searchBuilder)
    {
        $this->requestStack = $requestStack;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * On news list count items
     *
     * @param array          $archives
     * @param bool|null      $featured
     * @param ModuleNewsList $module
     *
     * @return int
     */
    public function onNewsListCountItems(array $archives, $featured, ModuleNewsList $module)
    {
        if (($criteria = $this->getCriteria($archives, $featured, $module)) === null) {
            return 0;
        }

        return $criteria->getNewsModelAdapter()->countBy(
            $criteria->getColumns(),
            $criteria->getValues(),
            $criteria->getOptions()
        );
    }

    /**
     * On news list fetch items
     *
     * @param array          $archives
     * @param bool|null      $featured
     * @param int            $limit
     * @param int            $offset
     * @param ModuleNewsList $module
     *
     * @return Collection|null
     */
    public function onNewsListFetchItems(array $archives, $featured, $limit, $offset, ModuleNewsList $module)
    {
        if (($criteria = $this->getCriteria($archives, $featured, $module)) === null) {
            return null;
        }

        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        return $criteria->getNewsModelAdapter()->findBy(
            $criteria->getColumns(),
            $criteria->getValues(),
            $criteria->getOptions()
        );
    }

    /**
     * Get the criteria
     *
     * @param array          $archives
     * @param bool|null      $featured
     * @param ModuleNewsList $module
     *
     * @return Criteria|null
     */
    private function getCriteria(array $archives, $featured, ModuleNewsList $module)
    {
        return $this->searchBuilder->getCriteriaForModule(
            $archives,
            $featured,
            $module,
            $this->requestStack->getCurrentRequest()
        );
    }
}
