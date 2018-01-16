<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Catalog\Model\Indexer\Product\Flat\StateFactory;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Products
{

    /**
     * Products constructor.
     *
     * @param ProductCollectionFactory          $productCollectionFactory
     * @param ProductAttributeCollectionFactory $productAttributeCollectionFactory
     * @param StockHelper                       $stockHelper
     * @param StateFactory                      $productFlatState
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ProductAttributeCollectionFactory $productAttributeCollectionFactory,
        StockHelper $stockHelper,
        StateFactory $productFlatState
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->productFlatState = $productFlatState;
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param $config
     *
     * @return mixed
     */
    public function getCollection($config)
    {
        $flat = $config['flat'];
        $filters = $config['filters'];
        $attributes = $this->getAttributes($config);

        if (!$flat) {
            $productFlatState = $this->productFlatState->create(['isAvailable' => false]);
        } else {
            $productFlatState = $this->productFlatState->create(['isAvailable' => true]);
        }

        $collection = $this->productCollectionFactory
            ->create(['catalogProductFlatState' => $productFlatState])
            ->addStoreFilter($config['store_id'])
            ->addAttributeToFilter('status', Status::STATUS_ENABLED)
            ->addAttributeToSelect($attributes)
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addFinalPrice();

        if ($filters['limit'] > 0) {
            $collection->setPageSize($filters['limit']);
        }

        if (!empty($filters['visibility'])) {
            $collection->addAttributeToFilter('visibility', ['in' => $filters['visibility']]);
        }

        if (!empty($filters['stock'])) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        if (!empty($filters['category_ids'])) {
            if (!empty($filters['category_type'])) {
                $collection->addCategoriesFilter([$filters['category_type'] => $filters['category_ids']]);
            }
        }

        $collection->joinTable(
            'cataloginventory_stock_item',
            'product_id=entity_id',
            $this->getStockAttributes()
        );

        return $collection->load();
    }

    /**
     * @param $config
     *
     * @return array
     */
    public function getAttributes($config)
    {
        $attributes = [];

        $selected = $this->getProductAttributes();
        $selectedAttrs = $config['attributes'];
        foreach ($selectedAttrs as $selectedAtt) {
            if (!empty($selectedAtt['source'])) {
                $selected[] = $selectedAtt['source'];
            }
        }

        $productAttrs = $this->productAttributeCollectionFactory->create();
        foreach ($productAttrs as $productAttr) {
            if (in_array($productAttr->getAttributeCode(), $selected)) {
                $attributes[] = $productAttr->getAttributeCode();
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return [
            'entity_id',
            'image',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'status',
            'tax_class_id',
            'weight',
            'product_has_weight'
        ];
    }

    /**
     * @return array
     */
    public function getStockAttributes()
    {
        return ['qty', 'is_in_stock', 'manage_stock', 'use_config_manage_stock'];
    }
}
