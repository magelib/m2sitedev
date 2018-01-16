<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api\Data;

interface MethodInterface
{
    const EDT_DISPLAY_TYPE_DAYS = 0;
    const EDT_DISPLAY_TYPE_HOURS = 1;
    const EDT_DISPLAY_TYPE_DAYS_AND_HOURS = 2;

    const EDT_PLACEHOLDER_MIN = '{{min}}';
    const EDT_PLACEHOLDER_MAX = '{{max}}';

    /**
     * Retrieve method title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Flag: is the title replacement allowed
     * In case it is allowed - the title from a most prior rate will be used
     * (in a case valid rate is exists)
     *
     * @return int
     */
    public function getReplaceableTitle();

    /**
     * Retrieve method code
     *
     * @return string
     */
    public function getCode();

    /**
     * Corresponding carrier id
     *
     * @return int
     */
    public function getCarrierId();

    /**
     * Retrieve method ID
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Check is method active
     *
     * @return int|bool
     */
    public function getActive();

    /**
     * Default method price
     *
     * @return float (12,2)
     */
    public function getPrice();

    /**
     * Get Max price threshold
     *
     * @return float|null
     */
    public function getMaxPriceThreshold();

    /**
     * Get Min price threshold
     *
     * @return float|null
     */
    public function getMinPriceThreshold();

    /**
     * Default method cost
     *
     * @return float (12,2)
     */
    public function getCost();

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
     * Check is we should disable this method when there are no valid rates
     *
     * @return int|bool
     */
    public function getDisabledWithoutValidRates();

    /**
     * Multiple rates price calculation method
     * @see \MageWorx\ShippingRules\Model\Config\Source\MultipleRatesPrice::toOptionArray()
     *
     * @return int
     */
    public function getMultipleRatesPrice();

    /**
     * Is free shipping by a third party extension allowed (like sales rule)
     *
     * @return int
     */
    public function getAllowFreeShipping();

    /**
     * Min estimated delivery time (can be overwritten by a value form a rate, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMin();

    /**
     * Max estimated delivery time (can be overwritten by a value form a rate, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMax();

    /**
     * Flag: is replacing of the estimated delivery time allowed (from a valid rates)
     *
     * @return int
     */
    public function getReplaceableEstimatedDeliveryTime();

    /**
     * How an estimated delivery time values would be visible for the customer?
     *
     * Possible values:
     * DAYS (rounded) - MethodInterface::EDT_DISPLAY_TYPE_DAYS
     * HOURS - MethodInterface::EDT_DISPLAY_TYPE_HOURS
     * DAYS & HOURS - MethodInterface::EDT_DISPLAY_TYPE_DAYS_AND_HOURS
     *
     * @return int
     */
    public function getEstimatedDeliveryTimeDisplayType();

    /**
     * Flag: should be the Estimated Delivery Time displayed for the customer or not
     *
     * @return int
     */
    public function getShowEstimatedDeliveryTime();

    /**
     * Markup for the EDT message.
     * You can use variables {{min}} {{max}} which will be replaced by a script to the corresponding values
     * from a method or rate.
     *
     * {{min}} - MethodInterface::EDT_PLACEHOLDER_MIN
     * {{max}} - MethodInterface::EDT_PLACEHOLDER_MAX
     *
     * @return string
     */
    public function getEstimatedDeliveryTimeMessage();
}
