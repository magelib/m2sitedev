<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use MageWorx\ShippingRules\Api\Data\CarrierInterface;
use MageWorx\ShippingRules\Model\ResourceModel\Carrier as CarrierResource;

/**
 * Class Carrier
 *
 * @method Carrier setCarrierId(int $id)
 * @method Carrier setCarrierCode(string $code)
 * @method Carrier setName(string $name)
 * @method Carrier setTitle(string $title)
 * @method CarrierResource _getResource()
 * @method CarrierResource getResource()
 *
 */
class Carrier extends AbstractModel implements CarrierInterface
{
    const CURRENT_CARRIER = 'current_carrier';

    const CARRIER_TABLE_NAME = 'mageworx_shippingrules_carrier';
    const METHOD_TABLE_NAME = 'mageworx_shippingrules_methods';
    const RATE_TABLE_NAME = 'mageworx_shippingrules_rates';
    const CARRIER_LABELS_TABLE_NAME = 'mageworx_shippingrules_carrier_label';
    const METHOD_LABELS_TABLE_NAME = 'mageworx_shippingrules_methods_label';
    const RATE_LABELS_TABLE_NAME = 'mageworx_shippingrules_rates_label';
    const METHOD_STORE_SPECIFIC_EDT_MESSAGE_TABLE_NAME = 'mageworx_shippingrules_method_edt_store_specific_message';

    const DEFAULT_MODEL = 'MageWorx\ShippingRules\Model\Carrier\Artificial';
    const DEFAULT_TYPE = 'I';
    const DEFAULT_ERROR_MESSAGE =
        'This shipping method is not available. To use this shipping method, please contact us.';

    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Method\Collection
     */
    protected $methodsCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\ShippingRules\Model\ResourceModel\Carrier');
        $this->setIdFieldName('carrier_id');
    }

    /**
     * Add store label if exists
     */
    public function afterLoad()
    {
        $this->getResource()->afterLoad($this);
        $storeId = $this->storeManager->getStore()->getId();
        $label = $this->getStoreLabel($storeId);
        if ($label) {
            $this->setTitle($label);
        }
        parent::afterLoad();
    }

    /**
     * @return ResourceModel\Method\Collection
     */
    public function getMethodsCollection()
    {
        return $this->methodsCollection;
    }

    /**
     * @param ResourceModel\Method\Collection $methods
     * @return $this
     */
    public function setMethodsCollection(\MageWorx\ShippingRules\Model\ResourceModel\Method\Collection $methods)
    {
        $this->methodsCollection = $methods;
        return $this;
    }

    /**
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = [];

        if (!$dataObject->getCarrierCode()) {
            $errors[] = __('Carrier code is required');
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Get Carrier label by specified store
     *
     * @param \Magento\Store\Model\Store|int|bool|null $store
     * @return string|bool
     */
    public function getStoreLabel($store = null)
    {
        $storeId = $this->storeManager->getStore($store)->getId();
        $labels = (array)$this->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve carrier store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }

        return $this->_getData('store_labels');
    }

    /**
     * Initialize carrier model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }

        return $this;
    }

    /**
     * Retrieve corresponding model name\path
     *
     * @return string
     */
    public function getModel()
    {
        return $this->getData('model');
    }

    /**
     * Check is carrier active
     *
     * @return int|bool
     */
    public function getActive()
    {
        return $this->getData('active');
    }

    /**
     * sallowspecific
     *
     * @return int
     */
    public function getSallowspecific()
    {
        return $this->getData('sallowspecific');
    }

    /**
     * Carrier type
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * Carrier error message
     *
     * @return string
     */
    public function getSpecificerrmsg()
    {
        return $this->getData('specificerrmsg');
    }

    /**
     * Default carrier price
     *
     * @return float (12,2)
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * Get created at date
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * Get last updated date
     *
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    /**
     * Retrieve carrier name
     *
     * If name is no declared, then default_name is used
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Retrieve carrier title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Retrieve carrier code
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->getData('carrier_code');
    }

    /**
     * Retrieve carrier ID
     *
     * @return int
     */
    public function getCarrierId()
    {
        return $this->getData('carrier_id');
    }
}
