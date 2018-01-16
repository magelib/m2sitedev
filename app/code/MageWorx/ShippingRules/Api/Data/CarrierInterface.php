<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Api\Data;

interface CarrierInterface
{
    /**
     * Retrieve carrier name
     *
     * If name is no declared, then default_name is used
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieve carrier title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Retrieve carrier code
     *
     * @return string
     */
    public function getCarrierCode();

    /**
     * Retrieve corresponding model name\path
     *
     * @return string
     */
    public function getModel();

    /**
     * Retrieve carrier ID
     *
     * @return int
     */
    public function getCarrierId();

    /**
     * Check is carrier active
     *
     * @return int|bool
     */
    public function getActive();

    /**
     * sallowspecific
     *
     * @return int
     */
    public function getSallowspecific();

    /**
     * Carrier type
     *
     * @return string
     */
    public function getType();

    /**
     * Carrier error message
     *
     * @return string
     */
    public function getSpecificerrmsg();

    /**
     * Default carrier price
     *
     * @return float (12,2)
     */
    public function getPrice();

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
}
