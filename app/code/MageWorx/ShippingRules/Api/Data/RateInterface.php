<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api\Data;

interface RateInterface
{
    /**
     * Retrieve rate ID
     *
     * @return int
     */
    public function getRateId();

    /**
     * Get id of the corresponding method
     *
     * @return int
     */
    public function getMethodId();

    /**
     * Get priority of the rate (sort order)
     *
     * @return int
     */
    public function getPriority();

    /**
     * Check is rate active
     *
     * @return int|bool
     */
    public function getActive();

    /**
     * Get price calculation method
     *
     * @return int
     */
    public function getRateMethodPrice();

    /**
     * Retrieve rate name
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve corresponding country id
     *
     * @return string
     */
    public function getCountryId();

    /**
     * Get region plain name
     *
     * @return string
     */
    public function getRegion();

    /**
     * Get id of region
     *
     * @return string
     */
    public function getRegionId();

    /**
     * Get conditions zip from
     *
     * @return string
     */
    public function getZipFrom();

    /**
     * Get conditions zip to
     *
     * @return string
     */
    public function getZipTo();

    /**
     * Get conditions price from
     *
     * @return float
     */
    public function getPriceFrom();

    /**
     * Get conditions price to
     *
     * @return float
     */
    public function getPriceTo();

    /**
     * Get conditions qty from
     *
     * @return float
     */
    public function getQtyFrom();

    /**
     * Get conditions qty to
     *
     * @return float
     */
    public function getQtyTo();

    /**
     * Get conditions weight from
     *
     * @return float
     */
    public function getWeightFrom();

    /**
     * Get conditions weight to
     *
     * @return float
     */
    public function getWeightTo();

    /**
     * Get rates price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get rates price per each product in cart
     *
     * @return float
     */
    public function getPricePerProduct();

    /**
     * Get rates price per each item in cart
     *
     * @return float
     */
    public function getPricePerItem();

    /**
     * Get rates price percent per each product in cart
     *
     * @return float
     */
    public function getPricePercentPerProduct();

    /**
     * Get rates price percent per each item in cart
     *
     * @return float
     */
    public function getPricePercentPerItem();

    /**
     * Get item price percent
     *
     * @return float
     */
    public function getItemPricePercent();

    /**
     * Price per each unit of weight
     *
     * @return float
     */
    public function getPricePerWeight();

    /**
     * Get created at date
     *
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * Get last updated date
     *
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMin();

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMax();
}
