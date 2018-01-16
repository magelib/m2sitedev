<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use MageWorx\ShippingRules\Model\Carrier\Method;

/**
 * Extended Method CRUD interface.
 * @api
 */
interface MethodRepositoryInterface
{
    /**
     * Save method.
     *
     * @param Method $method
     * @return Method
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Method $method);

    /**
     * Retrieve method.
     *
     * @param int $methodId
     * @return Method
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($methodId);

    /**
     * Retrieve methods matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete method.
     *
     * @param Method $method
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Method $method);

    /**
     * Delete method by ID.
     *
     * @param int $methodId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($methodId);

    /**
     * Get empty Method
     *
     * @return Method|Data\MethodInterface
     */
    public function getEmptyEntity();
}
