<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\StringUtils;
use MageWorx\ShippingRules\Helper\Data as Helper;

abstract class AbstractResourceModel extends AbstractDb
{
    protected $priceFields = [];

    /**
     * Magento string lib
     *
     * @var StringUtils
     */
    protected $string;

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param StringUtils $string
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Helper $helper,
        $connectionName = null
    ) {
        $this->string = $string;
        $this->helper = $helper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->getPriceFields() as $field) {
            $data = $object->getData($field);
            $updatedData = $this->helper->getAmount($data);
            $object->setData($field, (float)$updatedData);
        }

        return parent::_beforeSave($object);
    }

    /**
     * Returns fields with a price type (with $)
     *
     * @return array
     */
    public function getPriceFields()
    {
        return $this->priceFields;
    }
}