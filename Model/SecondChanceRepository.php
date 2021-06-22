<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * It is available through the world-wide-web at this URL:
 * https://tldrlegal.com/license/mit-license
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@buckaroo.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@buckaroo.nl for more information.
 *
 * @copyright Copyright (c) Buckaroo B.V.
 * @license   https://tldrlegal.com/license/mit-license
 */
namespace Buckaroo\Magento2\Model;

use Buckaroo\Magento2\Api\Data\SecondChanceInterface;
use Buckaroo\Magento2\Model\ResourceModel\SecondChance as SecondChanceResource;
use Buckaroo\Magento2\Model\ResourceModel\SecondChance\Collection as SecondChanceCollection;
use Buckaroo\Magento2\Model\ResourceModel\SecondChance\CollectionFactory as SecondChanceCollectionFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SecondChanceRepository
{
    /** @var SecondChanceResource */
    protected $resource;

    /** @var SecondChanceFactory */
    protected $SecondChanceFactory;

    /** @var SecondChanceCollectionFactory */
    protected $SecondChanceCollectionFactory;

    /** @var SearchResultsInterfaceFactory */
    protected $searchResultsFactory;

    public function __construct(
        SecondChanceResource $resource,
        SecondChanceFactory $SecondChanceFactory,
        SecondChanceCollectionFactory $SecondChanceCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource                      = $resource;
        $this->SecondChanceCollectionFactory = $SecondChanceCollectionFactory;
        $this->SecondChanceFactory           = $SecondChanceFactory;
        $this->searchResultsFactory          = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SecondChanceInterface $SecondChance)
    {
        try {
            $this->resource->save($SecondChance);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $SecondChance;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($SecondChanceId)
    {
        $SecondChance = $this->SecondChanceFactory->create();
        $SecondChance->load($SecondChanceId);

        if (!$SecondChance->getId()) {
            throw new NoSuchEntityException(__('SecondChance with id "%1" does not exist.', $SecondChanceId));
        }

        return $SecondChance;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteria $searchCriteria)
    {
        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var SecondChanceCollection $collection */
        $collection = $this->SecondChanceCollectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $this->handleFilterGroups($filterGroup, $collection);
        }

        $searchResults->setTotalCount($collection->getSize());
        $this->handleSortOrders($searchCriteria, $collection);

        $items = $this->getSearchResultItems($searchCriteria, $collection);
        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param SecondChanceCollection                        $collection
     */
    private function handleFilterGroups($filterGroup, $collection)
    {
        $fields     = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition    = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[]     = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @param SecondChanceCollection $collection
     */
    private function handleSortOrders($searchCriteria, $collection)
    {
        $sortOrders = $searchCriteria->getSortOrders();

        if (!$sortOrders) {
            return;
        }

        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $collection->addOrder(
                $sortOrder->getField(),
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @param SecondChanceCollection $collection
     *
     * @return array
     */
    private function getSearchResultItems($searchCriteria, $collection)
    {
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $items = [];

        foreach ($collection as $testieModel) {
            $items[] = $testieModel;
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(SecondChanceInterface $SecondChance)
    {
        try {
            $this->resource->delete($SecondChance);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($SecondChanceId)
    {
        $SecondChance = $this->getById($SecondChanceId);

        return $this->delete($SecondChance);
    }
}
