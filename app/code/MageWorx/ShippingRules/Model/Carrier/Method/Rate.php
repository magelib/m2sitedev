<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\ShippingRules\Model\Carrier\Method;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use MageWorx\ShippingRules\Api\Data\RateInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address\RateRequest;
use MageWorx\ShippingRules\Model\Config\Source\Locale\Country;
use Magento\Directory\Model\RegionFactory;
use MageWorx\ShippingRules\Model\ResourceModel\Rate as RateResource;

/**
 * Class Rate
 *
 * @method Rate setMethodId(int)
 * @method Rate setCountryId(string $countryId)
 * @method Rate setRegionId(string $regionId)
 * @method Rate setRegion(string $region)
 * @method Rate setTitle(string $title)
 * @method Rate setPriority(int $priority)
 * @method Rate setRateMethodPrice(int $rateMethodPrice)
 * @method bool hasStoreLabels()
 * @method Rate setStoreLabels(array $labels)
 * @method RateResource _getResource()
 * @method Rate setEstimatedDeliveryTimeMin($float)
 * @method Rate setEstimatedDeliveryTimeMax($float)
 *
 */
class Rate extends AbstractModel implements RateInterface
{
    const CURRENT_RATE = 'current_rate';

    const PRICE_CALCULATION_OVERWRITE = 0;
    const PRICE_CALCULATION_SUM = 1;

    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRIORITY = 0;
    const MULTIPLE_RATES_PRICE_CALCULATION_MAX_PRICE = 1;
    const MULTIPLE_RATES_PRICE_CALCULATION_MIN_PRICE = 2;
    const MULTIPLE_RATES_PRICE_CALCULATION_SUM_UP = 3;

    const DELIMITER = ',';

    /**
     * @var array
     */
    protected $preparedCountryIds = [];

    /**
     * @var bool
     */
    protected $preparedCountryIdsFlag = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageWorx\ShippingRules\Helper\Data
     */
    protected $helper;

    /**
     * @var Country
     */
    protected $countryList;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirer;

    /**
     * @var bool
     */
    protected $methodPriceWasAdded = false;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param Country $countryList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\ShippingRules\Helper\Data $helper,
        Country $countryList,
        RegionFactory $regionFactory,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->countryList = $countryList;
        $this->regionFactory = $regionFactory;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\ShippingRules\Model\ResourceModel\Rate');
        $this->setIdFieldName('rate_id');
    }

    /**
     * After load
     */
    public function afterLoad()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $label = $this->getStoreLabel($storeId);
        if ($label) {
            $this->setTitle($label);
        }
        parent::afterLoad();
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getRegionId()) {
            /** @var \Magento\Directory\Model\Region $region */
            $region = $this->regionFactory->create()->load($this->getRegionId());
            $this->setRegion($region->getCode());
        } elseif ($this->getCountryId() && !$this->getRegionId()) {
            try {
                $countryInfo = $this->countryInformationAcquirer->getCountryInfo($this->getCountryId());
                $availableRegions = $countryInfo->getAvailableRegions();
                if (!empty($availableRegions)) {
                    $this->setRegion(null);
                }
            } catch (\Exception $e) {
                // Workaround for the non-normal country ids like Africa, EU etc.
                // Because the countryInformationAcquirer has no info about them and throws exception
                return parent::beforeSave();
            }
        }

        return parent::beforeSave();
    }

    /**
     * Get Method label by specified store
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
     * Set if not yet and retrieve method store labels
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
     * Validate model data
     *
     * @param DataObject $dataObject
     * @return bool|array
     */
    public function validateData(DataObject $dataObject)
    {
        $errors = [];

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $method
     * @param RateRequest $request
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    public function applyRateToMethod(
        \Magento\Quote\Model\Quote\Address\RateResult\Method $method,
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
    
        $result = $this->getCalculatedPrice($request, $methodData);
        // Sum up rate prices
        if ($methodData->getMultipleRatesPrice() === Rate::MULTIPLE_RATES_PRICE_CALCULATION_SUM_UP) {
            $result += $method->getPrice();
        }

        if ($methodData->getMaxPriceThreshold() !== null &&
            $methodData->getMaxPriceThreshold() > 0 &&
            $result > $methodData->getMaxPriceThreshold()
        ) {
            $method->setPrice($methodData->getMaxPriceThreshold());
        } elseif ($methodData->getMinPriceThreshold() !== null &&
            $result < $methodData->getMinPriceThreshold() &&
            $methodData->getMinPriceThreshold() > 0
        ) {
            $method->setPrice($methodData->getMinPriceThreshold());
        } else {
            $method->setPrice($result);
        }

        // Change method title (if it is allowed by a method config)
        if ($methodData->getReplaceableTitle()) {
            if ($this->getStoreLabel()) {
                $method->setMethodTitle($this->getStoreLabel());
            } elseif ($this->getTitle()) {
                $method->setMethodTitle($this->getTitle());
            }
        }

        // Change Estimated Delivery time
        if ($methodData->isNeedToDisplayEstimatedDeliveryTime() && $methodData->getReplaceableEstimatedDeliveryTime()) {
            $methodData->setEstimatedDeliveryTimeMinByRate($this->getEstimatedDeliveryTimeMin());
            $methodData->setEstimatedDeliveryTimeMaxByRate($this->getEstimatedDeliveryTimeMax());
        }

        return $method;
    }

    /**
     * Get calculated rate's price
     *
     * @param RateRequest $request
     * @param \MageWorx\ShippingRules\Model\Carrier\Method $methodData
     * @return mixed|number
     */
    public function getCalculatedPrice(
        RateRequest $request,
        \MageWorx\ShippingRules\Model\Carrier\Method $methodData
    ) {
    
        $requestItemsCount = 0;
        $requestProductsCount = 0;
        foreach ($request->getAllItems() as $requestItem) {
            if ($requestItem->getParentItemId()) {
                continue;
            }
            $requestItemsCount += 1;
            $requestProductsCount += $requestItem->getQty();
        }
        $requestItemsCost = $this->calculateItemsTotalPrice($request->getAllItems());

        $price['base_price'] = $this->getPrice();
        $price['per_product'] = $requestProductsCount * $this->getPricePerProduct();
        $price['per_item'] = $requestItemsCount * $this->getPricePerItem();
        $price['percent_per_product'] = $requestProductsCount * $this->getPricePercentPerProduct() / 100;
        $price['percent_per_item'] = $requestItemsCount * $this->getPricePercentPerItem() / 100;
        $price['item_price_percent'] = $requestItemsCost * $this->getItemPricePercent() / 100;
        $price['per_weight'] = $request->getPackageWeight() * $this->getPricePerWeight();

        $result = array_sum($price);
        // Method price could be added only once
        if ($this->getRateMethodPrice() == self::PRICE_CALCULATION_SUM && !$this->methodPriceWasAdded) {
            $this->methodPriceWasAdded = true;
            $result += $methodData->getData('price');
        }

        return $result;
    }

    /**
     * @param $items
     * @return float
     */
    public function calculateItemsTotalPrice($items)
    {
        $totalPrice = 0.0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $totalPrice += $item->getBaseRowTotal(); // @TODO: with tax? without discount?
            // @TODO: possible add settings to the module config
        }

        return $totalPrice;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequest(RateRequest $request)
    {
        // Not active rates are invalid
        if (!$this->getActive()) {
            return false;
        }

        // Validate country
        if (!$this->validateRequestByCountryId($request)) {
            return false;
        }

        // Validate region
        if ($this->getRegionId() && $request->getDestRegionId() != $this->getRegionId()) {
            return false;
        } elseif ($this->getRegion() && $request->getDestRegionCode() != $this->getRegion()) {
            return false;
        }

        if (!$this->validateRequestByZipCode($request)) {
            return false;
        }

        if (!$this->validateRequestByPrice($request)) {
            return false;
        }

        if (!$this->validateRequestByQty($request)) {
            return false;
        }

        if (!$this->validateRequestByWeight($request)) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByCountryId(RateRequest $request)
    {
        $destinationCountryId = $request->getDestCountryId();
        if ($destinationCountryId == Country::CODE_WORLD) {
            return true;
        }

        $rateCountryIds = $this->getRateCountryIdAsArray();
        if (!in_array($destinationCountryId, $rateCountryIds)) {
            return false;
        }

        return true;
    }

    protected function getRateCountryIdAsArray()
    {
        if ($this->preparedCountryIdsFlag) {
            return $this->preparedCountryIds;
        }

        if (is_array($this->getCountryId())) {
            $this->preparedCountryIds = $this->getCountryId();
        } elseif (mb_stripos($this->getCountryId(), static::DELIMITER) !== false) {
            $this->preparedCountryIds = explode(static::DELIMITER, $this->getCountryId());
        } elseif (in_array($this->getCountryId(), ['EU', 'eu', 'Eu', 'eU'])) {
            $this->preparedCountryIds = $this->helper->getEuCountries();
        } elseif (preg_match('/^\d{0,3}$/', $this->getCountryId())) {
            $this->preparedCountryIds = $this->helper->resolveCountriesByDigitCode($this->getCountryId());
        } else {
            $this->preparedCountryIds = [$this->getCountryId()];
        }

        $this->preparedCountryIdsFlag = true;

        return $this->preparedCountryIds;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByZipCode(RateRequest $request)
    {
        if (!$this->getZipFrom() && !$this->getZipTo()) {
            return true;
        }

        $requestZip = mb_strtoupper($request->getDestPostcode());
        if ($this->getZipFrom() == $this->getZipTo() &&
            $requestZip == $this->getZipFrom()
        ) {
            return true;
        }

        if ($requestZip < $this->getZipFrom()) {
            return false;
        }

        if ($this->getZipTo() && $requestZip > $this->getZipTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByPrice(RateRequest $request)
    {
        if (!$this->getPriceFrom() && !$this->getPriceTo()) {
            return true;
        }

        $requestPrice = $request->getPackageValue();
        if ($this->getPriceFrom() == $this->getPriceTo() && $requestPrice == $this->getPriceFrom()) {
            return true;
        }

        if ($requestPrice < $this->getPriceFrom()) {
            return false;
        }

        if ($this->getPriceTo() != 0 && $requestPrice > $this->getPriceTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByQty(RateRequest $request)
    {
        if (!$this->getQtyFrom() && !$this->getQtyTo()) {
            return true;
        }

        $requestQty = $request->getPackageQty();
        if ($this->getQtyFrom() == $this->getQtyTo() && $requestQty == $this->getQtyFrom()) {
            return true;
        }

        if ($requestQty < $this->getQtyFrom()) {
            return false;
        }

        if ($this->getQtyTo() != 0 && $requestQty > $this->getQtyTo()) {
            return false;
        }

        return true;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    public function validateRequestByWeight(RateRequest $request)
    {
        if (!$this->getWeightFrom() && !$this->getWeightTo()) {
            return true;
        }

        $requestWeight = $request->getPackageWeight();
        if ($this->getWeightFrom() == $this->getWeightTo() && $requestWeight == $this->getWeightFrom()) {
            return true;
        }

        if ($requestWeight < $this->getWeightFrom()) {
            return false;
        }

        if ($this->getWeightTo() != 0 && $requestWeight > $this->getWeightTo()) {
            return false;
        }

        return true;
    }

    /**
     * Returns zip_from data in uppercase
     *
     * @return string
     */
    public function getZipFrom()
    {
        return mb_strtoupper($this->getData('zip_from'));
    }

    /**
     * Returns zip_to data in uppercase
     *
     * @return string
     */
    public function getZipTo()
    {
        return mb_strtoupper($this->getData('zip_to'));
    }

    /**
     * Retrieve rate ID
     *
     * @return int
     */
    public function getRateId()
    {
        return $this->getData('rate_id');
    }

    /**
     * Get id of the corresponding method
     *
     * @return int
     */
    public function getMethodId()
    {
        return $this->getData('method_id');
    }

    /**
     * Get priority of the rate (sort order)
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->getData('priority');
    }

    /**
     * Check is rate active
     *
     * @return int|bool
     */
    public function getActive()
    {
        return $this->getData('active');
    }

    /**
     * Get price calculation method
     *
     * @return int
     */
    public function getRateMethodPrice()
    {
        return $this->getData('rate_method_price');
    }

    /**
     * Retrieve rate name
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Retrieve corresponding country id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData('country_id');
    }

    /**
     * Get region plain name
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData('region');
    }

    /**
     * Get id of region
     *
     * @return string
     */
    public function getRegionId()
    {
        return $this->getData('region_id');
    }

    /**
     * Get conditions price from
     *
     * @return float
     */
    public function getPriceFrom()
    {
        return $this->getData('price_from');
    }

    /**
     * Get conditions price to
     *
     * @return float
     */
    public function getPriceTo()
    {
        return $this->getData('price_to');
    }

    /**
     * Get conditions qty from
     *
     * @return float
     */
    public function getQtyFrom()
    {
        return $this->getData('qty_from');
    }

    /**
     * Get conditions qty to
     *
     * @return float
     */
    public function getQtyTo()
    {
        return $this->getData('qty_to');
    }

    /**
     * Get conditions weight from
     *
     * @return float
     */
    public function getWeightFrom()
    {
        return $this->getData('weight_from');
    }

    /**
     * Get conditions weight to
     *
     * @return float
     */
    public function getWeightTo()
    {
        return $this->getData('weight_to');
    }

    /**
     * Get rates price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * Get rates price per each product in cart
     *
     * @return float
     */
    public function getPricePerProduct()
    {
        return $this->getData('price_per_product');
    }

    /**
     * Get rates price per each item in cart
     *
     * @return float
     */
    public function getPricePerItem()
    {
        return $this->getData('price_per_item');
    }

    /**
     * Get rates price percent per each product in cart
     *
     * @return float
     */
    public function getPricePercentPerProduct()
    {
        return $this->getData('price_percent_per_product');
    }

    /**
     * Get rates price percent per each item in cart
     *
     * @return float
     */
    public function getPricePercentPerItem()
    {
        return $this->getData('price_percent_per_item');
    }

    /**
     * Get item price percent
     *
     * @return float
     */
    public function getItemPricePercent()
    {
        return $this->getData('item_price_percent');
    }

    /**
     * Price per each unit of weight
     *
     * @return float
     */
    public function getPricePerWeight()
    {
        return $this->getData('price_per_weight');
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
     * Min estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMin()
    {
        $value = (float)$this->getData('estimated_delivery_time_min');

        return $value;
    }

    /**
     * Max estimated delivery time (usd to overwrite method value, visible at checkout & cart)
     *
     * @return float
     */
    public function getEstimatedDeliveryTimeMax()
    {
        $value = (float)$this->getData('estimated_delivery_time_max');

        return $value;
    }
}
