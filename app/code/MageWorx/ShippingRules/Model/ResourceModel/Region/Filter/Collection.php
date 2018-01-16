<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model\ResourceModel\Region\Filter;

use MageWorx\ShippingRules\Model\ResourceModel\Region\Collection as RegionCollection;

class Collection extends RegionCollection
{
    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);

        return $this;
    }
}
