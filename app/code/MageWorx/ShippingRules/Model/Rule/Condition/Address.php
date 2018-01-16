<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Rule\Condition;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Address
 *
 * @method Address setAttributeOption(array $array)
 * @method string getAttribute()
 * @method array getAttributeOption()
 */
class Address extends \MageWorx\ShippingRules\Model\Condition\AbstractAddress
{
    /**
     * @var \MageWorx\ShippingRules\Model\ResourceModel\Zone\CollectionFactory
     */
    protected $zoneCollectionFactory;

    /**
     * @var \Magento\Webapi\Controller\Rest\InputParamsResolver
     */
    protected $inputParamsResolver;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $addressFactory;

    /**
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param \MageWorx\ShippingRules\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver
     * @param \Magento\Quote\Model\Quote\AddressFactory $addressFactory
     * @param array $data
     */
    public function __construct(
        \MageWorx\ShippingRules\Helper\Data $helper,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        \MageWorx\ShippingRules\Model\ResourceModel\Zone\CollectionFactory $zoneCollectionFactory,
        \Magento\Webapi\Controller\Rest\InputParamsResolver $inputParamsResolver,
        \Magento\Quote\Model\Quote\AddressFactory $addressFactory,
        array $data = []
    ) {
        parent::__construct(
            $helper,
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
        $this->zoneCollectionFactory = $zoneCollectionFactory;
        $this->inputParamsResolver = $inputParamsResolver;
        $this->addressFactory = $addressFactory;
    }
    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'base_subtotal' => __('Subtotal (base)'),
            'base_subtotal_after_discount' => __('Subtotal with Discount (base)'),
            'base_discount_amount_for_validation' => __('Discount (base)'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'coupon_code' => __('Coupon Code'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
            'street' => __('Shipping Street'),
            'city' => __('Shipping City'),
            'zone_id' => __('Location Group (Shipping Zone)')
        ];

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        switch ($this->getAttribute()) {
            case 'base_subtotal':
            case 'base_subtotal_after_discount':
            case 'weight':
            case 'total_qty':
            case 'base_discount_amount_for_validation':
                return 'numeric';
            case 'country_id':
            case 'region_id':
            case 'zone_id':
                return 'select';
        }
        return 'string';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getAttribute()) {
            case 'country_id':
            case 'region_id':
            case 'zone_id':
                return 'select';
        }
        return 'text';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->_directoryCountry->toOptionArray();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                case 'zone_id':
                    /** @var \MageWorx\ShippingRules\Model\ResourceModel\Zone\Collection $zonesCollection */
                    $zonesCollection = $this->zoneCollectionFactory->create();
                    $options = $zonesCollection->toOptionArray();
                    break;

                default:
                    $options = [];
            }
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param AbstractModel|\Magento\Quote\Model\Quote\Address $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        /** @var \Magento\Quote\Model\Quote\Address|AbstractModel $address */
        $address = $model;
        if (!$address instanceof \Magento\Quote\Model\Quote\Address) {
            if ($model->getQuote()->isVirtual()) {
                $address = $model->getQuote()->getBillingAddress();
            } else {
                $address = $model->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && !$address->hasPaymentMethod()) {
            $address->setPaymentMethod($model->getQuote()->getPayment()->getMethod());
        }

        if ('base_subtotal_after_discount' == $this->getAttribute() && !$address->hasData($this->getAttribute())) {
            $baseSubtotalAfterDiscount = $this->calculateBaseSubtotalAfterDiscount($address);
            $address->setData('base_subtotal_after_discount', $baseSubtotalAfterDiscount);
        }

        if ($this->getAttribute() == 'zone_id' && !$address->hasData('zone_id')) {
            $this->addZoneToAddress($address);
        }

        /**
         * @important When API is used some of parameters could be found in the request but not in the address
         * from checkout session.
         */
        $this->resolveParametersFromApiRequest($address);

        if ($this->getAttribute() == 'coupon_code' && !$address->hasData('coupon_code')) {
            $address->setData('coupon_code', $address->getQuote()->getCouponCode());
        }

        if ($this->getAttribute() == 'base_discount_amount_for_validation' &&
            $address->getData('base_discount_amount') !== null
        ) {
            if ($address->getData('base_discount_amount') < 0) {
                $address->setData(
                    'base_discount_amount_for_validation',
                    $address->getData('base_discount_amount') * -1
                );
            } else {
                $address->setData(
                    'base_discount_amount_for_validation',
                    $address->getData('base_discount_amount')
                );
            }
        }

        /**
         * Prevent uncontrolled load of the address in the
         * @see \Magento\Rule\Model\Condition\AbstractCondition::validate
         */
        if (!$address->hasData($this->getAttribute())) {
            /** @var \Magento\Quote\Model\Quote\Address $addressLoaded */
            $addressLoaded = $this->addressFactory->create();
            $addressLoaded->getResource()->load($addressLoaded, $address->getId());
            $address->setData($this->getAttribute(), $addressLoaded->getData($this->getAttribute()));
        }

        $attributeValue = $address->getData($this->getAttribute());
        // Ignore parent actions
        $result = $this->validateAttribute($attributeValue);

        /** @var \MageWorx\ShippingRules\Model\Rule $rule */
        $rule = $this->getRule();
        if ($rule instanceof \MageWorx\ShippingRules\Model\Rule) {
            $rule->logConditions($this->getAttribute(), $result);
        }

        return $result;
    }

    /**
     * Resolve additional parameters which exists in api request address
     * but not saved in address inside quote.
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return void
     */
    private function resolveParametersFromApiRequest(\Magento\Quote\Model\Quote\Address $address)
    {
        /** @var \Magento\Quote\Model\Quote\Address[] $addressesFound */
        $addressesFound = [];
        try {
            $inputParams = $this->inputParamsResolver->resolve();
            if (empty($inputParams)) {
                return;
            }
            foreach ($inputParams as $param) {
                if ($param instanceof \Magento\Quote\Api\Data\AddressInterface) {
                    $addressesFound[] = $param;
                }
            }
            if (!count($addressesFound)) {
                return;
            }

            $priorAddress = $addressesFound[0];
            foreach ($addressesFound as $addressFound) {
                if ($addressFound->getAddressType() === \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                    $priorAddress = $addressFound;
                    break;
                }
            }
            $address->addData($priorAddress->getData());

            return;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return float
     */
    protected function calculateBaseSubtotalAfterDiscount(\Magento\Quote\Model\Quote\Address $address)
    {
        $baseSubtotalAfterDiscount = $address->getBaseSubtotalWithDiscount();

        return $baseSubtotalAfterDiscount;
    }

    /**
     * Detect valid zone for the address
     * Store valid zone id in address data
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return void
     */
    protected function addZoneToAddress($address)
    {
        /** @var \MageWorx\ShippingRules\Model\ResourceModel\Zone\Collection $zoneCollection */
        $zoneCollection = $this->zoneCollectionFactory->create();
        $zoneCollection->addStoreFilter($address->getQuote()->getStore()->getId())
            ->addIsActiveFilter()
            ->setOrder('priority', \Magento\Framework\Data\Collection\AbstractDb::SORT_ORDER_ASC);

        /** @var \MageWorx\ShippingRules\Model\Zone $zone */
        foreach ($zoneCollection as $zone) {
            if ($zone->validate($address)) {
                $address->setData('zone_id', $zone->getId());
            }
        }
    }
}
