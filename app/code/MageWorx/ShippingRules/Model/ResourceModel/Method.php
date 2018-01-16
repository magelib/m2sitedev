<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\StringUtils;
use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use MageWorx\ShippingRules\Model\ResourceModel\Rate\CollectionFactory as RatesCollectionFactory;
use MageWorx\ShippingRules\Helper\Data as Helper;

class Method extends AbstractResourceModel
{
    /**
     * @var RatesCollectionFactory
     */
    protected $rateCollectionFactory;

    protected $priceFields = [
        'max_price_threshold',
        'min_price_threshold',
        'price',
        'cost'
    ];

    /**
     * @param Context $context
     * @param StringUtils $string
     * @param Rate\CollectionFactory $rateCollectionFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StringUtils $string,
        Helper $helper,
        RatesCollectionFactory $rateCollectionFactory,
        $connectionName = null
    ) {
        $this->string = $string;
        $this->rateCollectionFactory = $rateCollectionFactory;
        parent::__construct($context, $string, $helper, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::METHOD_TABLE_NAME, 'entity_id');
    }

    /**
     * Add customer group ids and store ids to rule data after load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->addRates($object);

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method $object */
        if (!$object->getMaxPriceThreshold()) {
            $object->setMaxPriceThreshold(null);
        }
        if (!$object->getMinPriceThreshold()) {
            $object->setMinPriceThreshold(null);
        }
        parent::_beforeSave($object);

        return $this;
    }

    /**
     * Save method's associated store labels.
     * Save method's associated store specific EDT messages
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\Carrier\Method $object */
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        if ($object->hasEdtStoreSpecificMessages()) {
            $this->saveEdtStoreSpecificMessages($object->getId(), $object->getEdtStoreSpecificMessages());
        }

        return parent::_afterSave($object);
    }

    /**
     * Save method EDT store specific messages for the different store views
     *
     * @param int $methodId
     * @param array $messages
     * @throws \Exception
     * @return $this
     */
    public function saveEdtStoreSpecificMessages($methodId, $messages)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable(CarrierModel::METHOD_STORE_SPECIFIC_EDT_MESSAGE_TABLE_NAME);
        $connection = $this->getConnection();

        $data = [];
        foreach ($messages as $storeId => $message) {
            if ($message != '') {
                $data[] = ['method_id' => $methodId, 'store_id' => $storeId, 'message' => $message];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['message']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['method_id=?' => $methodId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing method store specific EDT messages
     *
     * @param int $methodId
     * @return array
     */
    public function getEdtStoreSpecificMessages($methodId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::METHOD_STORE_SPECIFIC_EDT_MESSAGE_TABLE_NAME),
            ['store_id', 'message']
        )->where(
            'method_id = :method_id'
        );
        return $this->getConnection()->fetchPairs($select, [':method_id' => $methodId]);
    }

    /**
     * Get method's EDT message by specific store id
     *
     * @param int $methodId
     * @param int $storeId
     * @return string
     */
    public function getEdtStoreSpecificMessage($methodId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::METHOD_STORE_SPECIFIC_EDT_MESSAGE_TABLE_NAME),
            'message'
        )->where(
            'method_id = :method_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':method_id' => $methodId, ':store_id' => $storeId]);
    }

    /**
     * Save carrier labels for different store views
     *
     * @param int $methodId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($methodId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable(CarrierModel::METHOD_LABELS_TABLE_NAME);
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($label != '') {
                $data[] = ['method_id' => $methodId, 'store_id' => $storeId, 'label' => $label];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['label']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['method_id=?' => $methodId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing carrier labels
     *
     * @param int $methodId
     * @return array
     */
    public function getStoreLabels($methodId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::METHOD_LABELS_TABLE_NAME),
            ['store_id', 'label']
        )->where(
            'method_id = :method_id'
        );
        return $this->getConnection()->fetchPairs($select, [':method_id' => $methodId]);
    }

    /**
     * Get carrier label by specific store id
     *
     * @param int $methodId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($methodId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::METHOD_LABELS_TABLE_NAME),
            'label'
        )->where(
            'method_id = :method_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':method_id' => $methodId, ':store_id' => $storeId]);
    }


    /**
     * Adds corresponding shipping rates to the method
     * @param AbstractModel $object
     */
    public function addRates(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $rates */
        $rates = $this->getRatesCollection($object);
        $object->setRates($rates->getItems());
    }

    /**
     * @param AbstractModel $object
     * @return Rate\Collection
     */
    public function getRatesCollection(AbstractModel $object)
    {
        /** @var \MageWorx\ShippingRules\Model\ResourceModel\Rate\Collection $rates */
        $rates = $this->rateCollectionFactory->create();
        $rates->addFieldToFilter('method_id', $object->getId());
        $rates->addOrder('priority');
        $object->setRatesCollection($rates);

        return $rates;
    }
}
