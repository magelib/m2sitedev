<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model\ResourceModel;

use MageWorx\ShippingRules\Model\Carrier as CarrierModel;
use MageWorx\ShippingRules\Model\Carrier\Method\Rate as RateModel;
use Magento\Framework\Model\AbstractModel;

class Rate extends AbstractResourceModel
{
    protected $priceFields = [
        'price_from',
        'price_to',
        'price',
        'price_per_product',
        'price_per_item',
        'price_per_weight'
    ];

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::RATE_TABLE_NAME, 'rate_id');
    }

    /**
     * Save rate's associated store labels.
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var RateModel $object */
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        return parent::_afterSave($object);
    }

    /**
     * Save rate labels for different store views
     *
     * @param int $rateId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($rateId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable(CarrierModel::RATE_LABELS_TABLE_NAME);
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($label != '') {
                $data[] = ['rate_id' => $rateId, 'store_id' => $storeId, 'label' => $label];
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
                $connection->delete($table, ['rate_id=?' => $rateId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing rate labels
     *
     * @param int $rateId
     * @return array
     */
    public function getStoreLabels($rateId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::RATE_LABELS_TABLE_NAME),
            ['store_id', 'label']
        )->where(
            'rate_id = :rate_id'
        );
        return $this->getConnection()->fetchPairs($select, [':rate_id' => $rateId]);
    }

    /**
     * Get rate label by specific store id
     *
     * @param int $rateId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($rateId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(CarrierModel::RATE_LABELS_TABLE_NAME),
            'label'
        )->where(
            'rate_id = :rate_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':rate_id' => $rateId, ':store_id' => $storeId]);
    }
}
