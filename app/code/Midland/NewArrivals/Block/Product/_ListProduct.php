<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Midland\NewArrivals\Block\Product;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct {

    protected function _getProductCollection() {
        if ($this->_productCollection === null) {
            $layer = $this->getLayer();
            /* @var $layer \Magento\Catalog\Model\Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($this->_storeManager->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if ($this->_coreRegistry->registry('product')) {
                // get collection of categories this product is associated with
                $categories = $this->_coreRegistry->registry('product')
                        ->getCategoryCollection()->setPage(1, 1)
                        ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                try {
                    $category = $this->categoryRepository->get($this->getCategoryId());
                } catch (NoSuchEntityException $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $current_category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        $catID = $current_category->getId();

        if ($catID == 571 && $catID !== "") {
            $request = $objectManager->create('Magento\Framework\App\Request\Http');
            $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $_productCollectionlastitem = $productCollection->create()->setPageSize(1)->addAttributeToSelect('*')->addAttributeToSort('entity_id', 'desc')
                    ->load();
            $lastid = $_productCollectionlastitem->getFirstItem()->getId();
            $startid = $lastid - 200;
            $_productCollection = $productCollection->create()
                    ->setPageSize(36)
                    ->setCurPage($request->getParam("p"))
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                    ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
					->addAttributeToFilter('image', array("neq"=>'no_selection'))
                    ->addAttributeToFilter('entity_id', array(
                'from' => $startid,
                'to' => $lastid
            ));
            if ($request->getParam("product_list_order")) {
                $_productCollection->addAttributeToSort($request->getParam("product_list_order"), "asc");
            } else {
                $_productCollection->addAttributeToSort('entity_id', "desc");
            }
            $_productCollection->addUrlRewrite();
            //
            $_productCollection->load();

            return $_productCollection;
        }


        return $this->_productCollection;
    }

    protected function _beforeToHtml() {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
                'catalog_block_product_list_collection', ['collection' => $this->_getProductCollection()]
        );

        $this->_getProductCollection()->load();

        return parent::_beforeToHtml();
    }

}
