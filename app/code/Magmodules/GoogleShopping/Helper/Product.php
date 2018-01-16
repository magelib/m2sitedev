<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\Image;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Filter\FilterManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Visibility;

use Magmodules\GoogleShopping\Helper\General as GeneralHelper;

class Product extends AbstractHelper
{

    protected $general;
    protected $source;
    protected $imgHelper;
    protected $eavConfig;
    protected $filter;
    protected $catalogProductTypeConfigurable;

    /**
     * Product constructor.
     *
     * @param Context       $context
     * @param Image         $imgHelper
     * @param General       $general
     * @param EavConfig     $eavConfig
     * @param FilterManager $filter
     * @param Configurable  $catalogProductTypeConfigurable
     */
    public function __construct(
        Context $context,
        Image $imgHelper,
        GeneralHelper $general,
        EavConfig $eavConfig,
        FilterManager $filter,
        Configurable $catalogProductTypeConfigurable
    ) {
        $this->imgHelper = $imgHelper;
        $this->general = $general;
        $this->eavConfig = $eavConfig;
        $this->filter = $filter;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        parent::__construct($context);
    }

    /**
     * @param $product
     * @param $parent
     * @param $config
     *
     * @return array
     */
    public function getDataRow($product, $parent, $config)
    {
        $dataRow = [];

        if (!$this->validateProduct($product, $parent, $config)) {
            return $dataRow;
        }

        foreach ($config['attributes'] as $type => $attribute) {
            $simple = '';
            $productData = $product;
            if ($attribute['parent'] && $parent) {
                $productData = $parent;
                $simple = $product;
            }
            if (($attribute['parent'] == 2) && !$parent) {
                continue;
            }
            if (!empty($attribute['source'])) {
                $dataRow[$attribute['label']] = $this->getAttributeValue(
                    $type,
                    $attribute,
                    $config,
                    $productData,
                    $simple
                );
            }
            if (!empty($attribute['static'])) {
                $dataRow[$attribute['label']] = $attribute['static'];
            }
            if (!empty($attribute['condition'])) {
                $dataRow[$attribute['label']] = $this->getCondition(
                    $dataRow[$attribute['label']],
                    $attribute['condition']
                );
            }
            if (!empty($attribute['collection'])) {
                if ($dataCollection = $this->getAttributeCollection($type, $config, $productData)) {
                    $dataRow = array_merge($dataRow, $dataCollection);
                }
            }
        }

        return $dataRow;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $parent
     * @param                                $config
     *
     * @return bool
     */
    public function validateProduct($product, $parent, $config)
    {
        $filters = $config['filters'];
        if (!empty($filters['exclude_parent'])) {
            if ($product->getTypeId() == 'configurable') {
                return false;
            }
        }
        if (!empty($parent)) {
            if ($parent->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
                return false;
            }
            if (!empty($filters['stock'])) {
                if ($parent->getIsInStock() == 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $type
     * @param $attribute
     * @param $config
     * @param $product
     * @param $simple
     *
     * @return mixed|string
     */
    public function getAttributeValue($type, $attribute, $config, $product, $simple)
    {
        switch ($type) {
            case 'link':
                $value = $this->getProductUrl($product, $simple, $config);
                break;
            case 'image_link':
                $value = $this->getImage($attribute, $config, $product);
                break;
            default:
                $value = $this->getValue($attribute, $product, $config['store_id']);
                break;
        }

        if (!empty($value)) {
            if (!empty($attribute['actions']) || !empty($attribute['max'])) {
                $value = $this->getFormat($value, $attribute['actions'], $attribute['max']);
            }
            if (!empty($attribute['suffix'])) {
                if (!empty($config[$attribute['suffix']])) {
                    $value .= $config[$attribute['suffix']];
                }
            }
        }

        return $value;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $simple
     * @param                                $config
     *
     * @return string
     */
    public function getProductUrl($product, $simple, $config)
    {
        $url = '';
        if ($requestPath = $product->getRequestPath()) {
            $url = $config['base_url'] . $requestPath;
        }

        if (!empty($config['utm_code'])) {
            $url .= $config['utm_code'];
        }

        if (!empty($simple)) {
            if ($product->getTypeId() == 'configurable') {
                $options = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                foreach ($options as $option) {
                    if ($id = $simple->getResource()->getAttributeRawValue(
                        $simple->getId(),
                        $option['attribute_code'],
                        $config['store_id']
                    )
                    ) {
                        $url_extra[] = $option['attribute_id'] . '=' . $id;
                    }
                }
            }
            if (!empty($url_extra)) {
                $url = $url . '#' . implode('&', $url_extra);
            }
        }

        return $url;
    }

    /**
     * @param                                $attribute
     * @param                                $config
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getImage($attribute, $config, $product)
    {
        $img = '';
        if ($source = $attribute['source']) {
            if ($product->getImage()) {
                $img = $config['url_type_media'] . 'catalog/product' . $product->getImage();
            }
        }

        return $img;
    }

    /**
     * @param $attribute
     * @param $product
     * @param $storeId
     *
     * @return string
     */
    public function getValue($attribute, $product, $storeId)
    {
        if ($attribute['type'] == 'select') {
            if ($attr = $product->getResource()->getAttribute($attribute['source'])) {
                $value = $product->getData($attribute['source']);
                return $attr->setStoreId($storeId)->getSource()->getOptionText($value);
            }
        }
        if ($attribute['type'] == 'multiselect') {
            if ($attr = $product->getResource()->getAttribute($attribute['source'])) {
                $value_text = [];
                $values = explode(',', $product->getData($attribute['source']));
                foreach ($values as $value) {
                    $value_text[] = $attr->setStoreId($storeId)->getSource()->getOptionText($value);
                }
                return implode('/', $value_text);
            }
        }

        return $product->getData($attribute['source']);
    }

    /**
     * @param        $value
     * @param string $actions
     * @param string $max
     *
     * @return mixed|string
     */
    public function getFormat($value, $actions = '', $max = '')
    {
        $actions = explode('_', $actions);
        if (in_array('striptags', $actions)) {
            $value = str_replace(["\r", "\n"], "", $value);
            $value = strip_tags($value);
        }
        if (in_array('number', $actions)) {
            $value = number_format($value, 2);
        }
        if ($max > 0) {
            $value = $this->filter->truncate($value, ['length' => $max]);
        }
        return $value;
    }

    /**
     * @param $value
     * @param $conditions
     *
     * @return bool
     */
    public function getCondition($value, $conditions)
    {
        foreach ($conditions as $condition) {
            $ex = explode(':', $condition);
            if ($value == $ex['0']) {
                return $ex[1];
            }
        }

        return false;
    }

    /**
     * @param $type
     * @param $config
     * @param $product
     *
     * @return array
     */
    public function getAttributeCollection($type, $config, $product)
    {
        if ($type == 'price') {
            return $this->getPriceCollection($config, $product);
        }

        return [];
    }

    /**
     * @param                                $config
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return array
     */
    public function getPriceCollection($config, $product)
    {
        $config = $config['price_config'];

        $price = floatval($product->getPriceInfo()->getPrice('regular_price')->getValue());
        $final_price = floatval($product->getPriceInfo()->getPrice('final_price')->getValue());
        $special_price = floatval($product->getPriceInfo()->getPrice('special_price')->getValue());

        $prices = [];
        $prices[$config['price']] = $this->formatPrice($price, $config);

        if ($price > $final_price) {
            $prices[$config['sales_price']] = $this->formatPrice($final_price, $config);
        }

        if ($special_price < $price) {
            if ($product->getSpecialFromDate() && $product->getSpecialToDate()) {
                $from = date('Y-m-d', strtotime($product->getSpecialFromDate()));
                $to = date('Y-m-d', strtotime($product->getSpecialToDate()));
                $prices[$config['sales_date_range']] = $from . '/' . $to;
            }
        }

        return $prices;
    }

    /**
     * @param $data
     * @param $config
     *
     * @return string
     */
    public function formatPrice($data, $config)
    {
        return number_format($data, 2, '.', '') . $config['currency'];
    }

    /**
     * @param        $attributes
     * @param string $parentAttributes
     *
     * @return array
     */
    public function addAttributeData($attributes, $parentAttributes)
    {
        $attributeData = [];
        foreach ($attributes as $key => $value) {
            $actions = (!empty($value['actions']) ? $value['actions'] : '');
            $parent = (!empty($value['parent']) ? $value['parent'] : '');

            if (!empty($value['source'])) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $value['source']);
                $frontendInput = $attribute->getFrontendInput();
                if ($frontendInput == 'textarea') {
                    if (!empty($action)) {
                        $actions = $actions . '_striptags';
                    } else {
                        $actions = 'striptags';
                    }
                }
            }

            $attributeData[$key] = [
                'label'      => (!empty($value['label']) ? $value['label'] : ''),
                'source'     => (!empty($value['source']) ? $value['source'] : ''),
                'static'     => (!empty($value['static']) ? $value['static'] : ''),
                'collection' => (!empty($value['collection']) ? $value['collection'] : ''),
                'type'       => (!empty($frontendInput) ? $frontendInput : ''),
                'actions'    => (!empty($actions) ? $actions : ''),
                'max'        => (!empty($value['max']) ? $value['max'] : ''),
                'suffix'     => (!empty($value['suffix']) ? $value['suffix'] : ''),
                'condition'  => (!empty($value['condition']) ? $value['condition'] : ''),
                'parent'     => (in_array($key, $parentAttributes) ? 1 : $parent),
            ];
        }

        return $attributeData;
    }

    /**
     * @param $productId
     *
     * @return bool
     */
    public function getParentId($productId)
    {
        $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($productId);
        if (isset($parentByChild[0])) {
            $id = $parentByChild[0];
            return $id;
        }
        return false;
    }
}
