<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Helper\Product as ProductHelper;
use Magmodules\GoogleShopping\Helper\Category as CategoryHelper;
use Magmodules\GoogleShopping\Helper\Feed as FeedHelper;

class Source extends AbstractHelper
{

    const XML_PATH_NAME_SOURCE = 'magmodules_googleshopping/data/name_attribute';
    const XML_PATH_DESCRIPTION_SOURCE = 'magmodules_googleshopping/data/description_attribute';
    const XML_PATH_CONDITION_TYPE = 'magmodules_googleshopping/data/condition_type';
    const XML_PATH_CONDITION_DEFAULT = 'magmodules_googleshopping/data/condition_default';
    const XML_PATH_CONDITION_SOURCE = 'magmodules_googleshopping/data/condition_attribute';
    const XML_PATH_GTIN_SOURCE = 'magmodules_googleshopping/data/gtin_attribute';
    const XML_PATH_BRAND_SOURCE = 'magmodules_googleshopping/data/brand_attribute';
    const XML_PATH_MPN_SOURCE = 'magmodules_googleshopping/data/mpn_attribute';
    const XML_PATH_COLOR_SOURCE = 'magmodules_googleshopping/data/color_attribute';
    const XML_PATH_MATERIAL_SOURCE = 'magmodules_googleshopping/data/material_attribute';
    const XML_PATH_PATTERN_SOURCE = 'magmodules_googleshopping/data/pattern_attribute';
    const XML_PATH_SIZE_SOURCE = 'magmodules_googleshopping/data/size_attribute';
    const XML_PATH_SIZETYPE_SOURCE = 'magmodules_googleshopping/data/sizetype_attribute';
    const XML_PATH_SIZESYTEM_SOURCE = 'magmodules_googleshopping/data/sizesystem_attribute';
    const XML_PATH_GENDER_SOURCE = 'magmodules_googleshopping/data/gender_attribute';
    const XML_PATH_EXTRA_FIELDS = 'magmodules_googleshopping/advanced/extra_fields';
    const XML_PATH_URL_UTM = 'magmodules_googleshopping/advanced/url_utm';
    const XML_PATH_SHIPPING = 'magmodules_googleshopping/advanced/shipping';
    const XML_PATH_LIMIT = 'magmodules_googleshopping/generate/limit';
    const XML_PATH_WEIGHT_UNIT = 'general/locale/weight_unit';
    const XML_PATH_CATEGORY = 'magmodules_googleshopping/data/category';
    const XML_PATH_VISBILITY = 'magmodules_googleshopping/filter/visbility_enabled';
    const XML_PATH_VISIBILITY_OPTIONS = 'magmodules_googleshopping/filter/visbility';

    const XML_PATH_CATEGORY_FILTER = 'magmodules_googleshopping/filter/category_enabled';
    const XML_PATH_CATEGORY_FILTER_TYPE = 'magmodules_googleshopping/filter/category_type';
    const XML_PATH_CATEGORY_IDS = 'magmodules_googleshopping/filter/category';

    const XML_PATH_STOCK = 'magmodules_googleshopping/filter/stock';
    const XML_PATH_RELATIONS_ENABLED = 'magmodules_googleshopping/advanced/relations';
    const XML_PATH_PARENT_ATTS = 'magmodules_googleshopping/advanced/parent_atts';

    protected $general;
    protected $product;
    protected $category;
    protected $feed;
    protected $storeManager;

    /**
     * Source constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param General               $general
     * @param Category              $category
     * @param Product               $product
     * @param Feed                  $feed
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        GeneralHelper $general,
        CategoryHelper $category,
        ProductHelper $product,
        FeedHelper $feed
    ) {
        $this->general = $general;
        $this->product = $product;
        $this->category = $category;
        $this->feed = $feed;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     *
     * @param $storeId
     * @param $type
     *
     * @return array
     */
    public function getConfig($storeId, $type)
    {
        $config = [];
        $config['type'] = $type;
        $config['store_id'] = $storeId;
        $config['flat'] = false;
        $config['attributes'] = $this->getAttributes($storeId);
        $config['price_config'] = $this->getPriceConfig();
        $config['url_type_media'] = $this->storeManager->getStore($storeId)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $config['base_url'] = $this->storeManager->getStore($storeId)->getBaseUrl();
        $config['feed_locations'] = $this->feed->getFeedLocation($storeId, $type);
        $config['utm_code'] = $this->general->getStoreValue(self::XML_PATH_URL_UTM, $storeId);
        $config['filters'] = $this->getProductFilters($storeId, $type);
        $config['weight_unit'] = $this->general->getStoreValue(self::XML_PATH_WEIGHT_UNIT, $storeId);
        $config['default_category'] = $this->general->getStoreValue(self::XML_PATH_CATEGORY, $storeId);
        $config['categories'] = $this->category->getCollection(
            $storeId,
            'googleshopping_cat',
            $config['default_category']
        );

        return $config;
    }

    /**
     * @param int    $storeId
     * @param string $type
     *
     * @return array
     */
    public function getAttributes($storeId = 0, $type = 'feed')
    {
        $attributes = [];
        $attributes['id'] = [
            'label'  => 'g:id',
            'source' => 'entity_id',
            'max'    => 50
        ];
        $attributes['name'] = [
            'label'  => 'g:title',
            'source' => $this->general->getStoreValue(self::XML_PATH_NAME_SOURCE, $storeId),
            'max'    => 150
        ];
        $attributes['description'] = [
            'label'  => 'g:description',
            'source' => $this->general->getStoreValue(self::XML_PATH_DESCRIPTION_SOURCE, $storeId),
            'max'    => 5000
        ];
        $attributes['link'] = [
            'label'  => 'g:link',
            'source' => 'product_url',
            'max'    => 2000,
            'parent' => 1
        ];
        $attributes['image_link'] = [
            'label'  => 'g:image_link',
            'source' => 'product_base_image',
            'max'    => 2000
        ];
        $attributes['price'] = [
            'label'      => 'g:price',
            'collection' => 'price'
        ];
        $attributes['brand'] = [
            'label'  => 'g:brand',
            'source' => $this->general->getStoreValue(self::XML_PATH_BRAND_SOURCE, $storeId),
            'max'    => 70
        ];
        $attributes['gtin'] = [
            'label'  => 'g:gtin',
            'source' => $this->general->getStoreValue(self::XML_PATH_GTIN_SOURCE, $storeId),
            'max'    => 50
        ];
        $attributes['model'] = [
            'label'  => 'g:mpn',
            'source' => $this->general->getStoreValue(self::XML_PATH_MPN_SOURCE, $storeId),
            'max'    => 70
        ];
        $attributes['condition'] = $this->getConditionSource($storeId);
        $attributes['color'] = [
            'label'  => 'g:color',
            'source' => $this->general->getStoreValue(self::XML_PATH_COLOR_SOURCE, $storeId),
            'max'    => 100
        ];
        $attributes['gender'] = [
            'label'  => 'g:gender',
            'source' => $this->general->getStoreValue(self::XML_PATH_GENDER_SOURCE, $storeId)
        ];
        $attributes['material'] = [
            'label'  => 'g:material',
            'source' => $this->general->getStoreValue(self::XML_PATH_MATERIAL_SOURCE, $storeId),
            'max'    => 200
        ];
        $attributes['pattern'] = [
            'label'  => 'g:pattern',
            'source' => $this->general->getStoreValue(self::XML_PATH_PATTERN_SOURCE, $storeId),
            'max'    => 100
        ];
        $attributes['size'] = [
            'label'  => 'g:size',
            'source' => $this->general->getStoreValue(self::XML_PATH_SIZE_SOURCE, $storeId),
            'max'    => 100
        ];
        $attributes['size_type'] = [
            'label'  => 'g:size_type',
            'source' => $this->general->getStoreValue(self::XML_PATH_SIZETYPE_SOURCE, $storeId)
        ];
        $attributes['size_system'] = [
            'label'  => 'g:size_system',
            'source' => $this->general->getStoreValue(self::XML_PATH_SIZESYTEM_SOURCE, $storeId)
        ];
        $attributes['weight'] = [
            'label'   => 'g:shipping_weight',
            'source'  => 'weight',
            'suffix'  => 'weight_unit',
            'actions' => 'number'
        ];
        $attributes['item_group_id'] = [
            'label'  => 'g:item_group_id',
            'source' => $attributes['id']['source'],
            'parent' => 2
        ];
        $attributes['is_bundle'] = [
            'label'     => 'g:is_bundle',
            'source'    => 'type_id',
            'condition' => ['bundle:yes']
        ];
        $attributes['availability'] = [
            'label'     => 'g:availability',
            'source'    => 'is_in_stock',
            'condition' => [
                '1:in stock',
                '0:out of stock'
            ]
        ];

        if ($extraFields = $this->getExtraFields($storeId)) {
            $attributes = array_merge($attributes, $extraFields);
        }

        if ($type != 'feed') {
            return $attributes;
        } else {
            $parentAttributes = $this->getParentAttributes($storeId);
            return $this->product->addAttributeData($attributes, $parentAttributes);
        }
    }

    /**
     * @param $storeId
     *
     * @return array|bool
     */
    public function getConditionSource($storeId)
    {
        $conditionType = $this->general->getStoreValue(self::XML_PATH_CONDITION_TYPE, $storeId);
        if ($conditionType == 'static') {
            return [
                'label'  => 'g:condition',
                'static' => $this->general->getStoreValue(self::XML_PATH_CONDITION_DEFAULT, $storeId)
            ];
        }
        if ($conditionType == 'attribute') {
            return [
                'label'  => 'g:condition',
                'source' => $this->general->getStoreValue(self::XML_PATH_CONDITION_SOURCE, $storeId)
            ];
        }

        return false;
    }

    /**
     * @param $storeId
     *
     * @return array
     */
    public function getExtraFields($storeId)
    {
        $extraFields = [];
        if ($attributes = $this->general->getStoreValue(self::XML_PATH_EXTRA_FIELDS, $storeId)) {
            $attributes = @unserialize($attributes);
            foreach ($attributes as $attribute) {
                $extraFields[$attribute['attribute']] = [
                    'label'  => $attribute['name'],
                    'source' => $attribute['attribute']
                ];
            }
        }

        return $extraFields;
    }

    /**
     * @param $storeId
     *
     * @return array|bool|mixed
     */
    public function getParentAttributes($storeId)
    {
        $enabled = $this->general->getStoreValue(self::XML_PATH_RELATIONS_ENABLED, $storeId);
        if ($enabled) {
            if ($attributes = $this->general->getStoreValue(self::XML_PATH_PARENT_ATTS, $storeId)) {
                $attributes = explode(',', $attributes);

                return $attributes;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getPriceConfig()
    {
        $priceFields = [];
        $priceFields['price'] = 'g:price';
        $priceFields['final_price'] = 'g:price';
        $priceFields['sales_price'] = 'g:sale_price';
        $priceFields['sales_date_range'] = 'g:sale_price_effective_date';
        $priceFields['currency'] = ' ' . $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        return $priceFields;
    }

    /**
     * @param $storeId
     * @param $type
     *
     * @return array
     */
    public function getProductFilters($storeId, $type)
    {
        $filters = [];

        $visibilityFilter = $this->general->getStoreValue(self::XML_PATH_VISBILITY, $storeId);
        if ($visibilityFilter) {
            $visibility = $this->general->getStoreValue(self::XML_PATH_VISIBILITY_OPTIONS, $storeId);
            $filters['visibility'] = explode(',', $visibility);
        } else {
            $filters['visibility'] = [
                Visibility::VISIBILITY_IN_CATALOG,
                Visibility::VISIBILITY_IN_SEARCH,
                Visibility::VISIBILITY_BOTH,
            ];
        }

        $relations = $this->general->getStoreValue(self::XML_PATH_RELATIONS_ENABLED, $storeId);
        if ($relations) {
            $filters['relations'] = 1;
            array_push($filters['visibility'], Visibility::VISIBILITY_NOT_VISIBLE);
        } else {
            $filters['relations'] = 0;
        }

        if ($type == 'preview') {
            $filters['limit'] = '100';
        } else {
            $filters['limit'] = (int)$this->general->getStoreValue(self::XML_PATH_LIMIT, $storeId);
        }

        if ($filters['relations'] == 1) {
            $filters['exclude_parent'] = 1;
        }

        $filters['stock'] = $this->general->getStoreValue(self::XML_PATH_STOCK, $storeId);

        $categoryFilter = $this->general->getStoreValue(self::XML_PATH_CATEGORY_FILTER, $storeId);
        if ($categoryFilter) {
            $categoryIds = $this->general->getStoreValue(self::XML_PATH_CATEGORY_IDS, $storeId);
            $filterType = $this->general->getStoreValue(self::XML_PATH_CATEGORY_FILTER_TYPE, $storeId);
            if (!empty($categoryIds) && !empty($filterType)) {
                $filters['category_ids'] = explode(',', $categoryIds);
                $filters['category_type'] = $filterType;
            }
        }

        return $filters;
    }

    /**
     * @param $dataRow
     * @param $product
     * @param $config
     *
     * @return string
     */
    public function reformatData($dataRow, $product, $config)
    {
        if ($identifierExists = $this->getIdentifierExists($dataRow)) {
            $dataRow = array_merge($dataRow, $identifierExists);
        }
        if ($categoryData = $this->getCategoryData($product, $config['categories'])) {
            $dataRow = array_merge($dataRow, $categoryData);
        }
        if ($shippingPrices = $this->getShippingPrices($dataRow, $config)) {
            $dataRow = array_merge($dataRow, $shippingPrices);
        }
        $xml = $this->getXmlFromArray($dataRow, 'item');

        return $xml;
    }

    /**
     * @param $dataRow
     *
     * @return array
     */
    public function getIdentifierExists($dataRow)
    {
        $identifier = 0;
        $identifierExists = [];

        if (!empty($dataRow['g:gtin'])) {
            $identifier++;
        }
        if (!empty($dataRow['g:brand'])) {
            $identifier++;
        }
        if (!empty($dataRow['g:model'])) {
            $identifier++;
        }
        if ($identifier < 2) {
            $identifierExists['g:identifier_exists'] = 'no';
        }

        return $identifierExists;
    }

    /**
     * @param $product
     * @param $categories
     *
     * @return array
     */
    public function getCategoryData($product, $categories)
    {
        $path = [];
        $level = 0;
        foreach ($product->getCategoryIds() as $catId) {
            if (!empty($categories[$catId])) {
                $category = $categories[$catId];
                if ($category['level'] > $level) {
                    $deepestCategory = $category;
                    $level = $category['level'];
                }
            }
        }
        if (!empty($deepestCategory)) {
            $path['g:product_type'] = implode(' > ', $deepestCategory['path']);
            $path['g:google_product_category'] = $deepestCategory['custom'];
        }

        return $path;
    }

    /**
     * @param $dataRow
     * @param $config
     *
     * @return array
     */
    public function getShippingPrices($dataRow, $config)
    {
        $shippingPrices = [];
        if ($shippingArray = $this->general->getStoreValue(self::XML_PATH_SHIPPING, $config['store_id'])) {
            $currency = $config['price_config']['currency'];
            $price = (!empty($dataRow['g:sales_price']) ? $dataRow['g:sales_price'] : $dataRow['g:price']);
            $price = str_replace($currency, '', $price);
            $i = 0;
            foreach (@unserialize($shippingArray) as $shipping) {
                if (($price >= $shipping['price_from']) && ($price <= $shipping['price_to'])) {
                    $shippingPrices['shipping' . $i] = [
                        'g:country' => $shipping['country'],
                        'g:service' => $shipping['service'],
                        'g:price'   => number_format($shipping['price'], 2, '.', '') . $currency
                    ];
                    $i++;
                }
            }
        }

        return $shippingPrices;
    }

    /**
     * @param $data
     * @param $type
     *
     * @return string
     */
    public function getXmlFromArray($data, $type)
    {
        $xml = '  <' . $type . '>' . PHP_EOL;
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $key = preg_replace('/[0-9]*$/', '', $key);
                $xml .= '   <' . $key . '>' . PHP_EOL;
                foreach ($value as $key2 => $value2) {
                    if (!empty($value2)) {
                        $xml .= '      <' . $key2 . '>' . htmlspecialchars($value2) . '</' . $key2 . '>' . PHP_EOL;
                    }
                }
                $xml .= '   </' . $key . '>' . PHP_EOL;
            } else {
                if (!empty($value)) {
                    $xml .= '   <' . $key . '>' . htmlspecialchars($value) . '</' . $key . '>' . PHP_EOL;
                }
            }
        }
        $xml .= '  </' . $type . '>' . PHP_EOL;

        return $xml;
    }
}
