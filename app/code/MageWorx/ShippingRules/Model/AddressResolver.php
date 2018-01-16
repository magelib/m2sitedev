<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Locale\ResolverInterface;
use MageWorx\ShippingRules\Helper\Data as Helper;
use Magento\Checkout\Model\Session as CheckoutSession;
use MageWorx\GeoIP\Model\Geoip;
use MageWorx\ShippingRules\Helper\Data;
use MageWorx\ShippingRules\Model\ZoneFactory;
use MageWorx\ShippingRules\Api\AddressResolverInterface;

/**
 * Class AddressResolver
 * @package MageWorx\ShippingRules\Model
 *
 * Used to resolve current customers address based on the selection in popup or on the geo ip database
 */
class AddressResolver implements AddressResolverInterface
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Geoip
     */
    protected $geoIp;

    /**
     * @var CollectionFactory
     */
    protected $regionCollectionFactory;

    /**
     * @var ZoneFactory
     */
    protected $zoneFactory;

    /**
     * @var \MageWorx\ShippingRules\Model\Zone|null
     */
    protected $zone;

    /**
     * ChannelButtons constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param ResolverInterface $localeResolver
     * @param Helper $helper
     * @param Geoip $geoip
     * @param CollectionFactory $regionCollectionFactory
     * @param ZoneFactory $zoneFactory
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ResolverInterface $localeResolver,
        Data $helper,
        Geoip $geoip,
        CollectionFactory $regionCollectionFactory,
        ZoneFactory $zoneFactory
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->helper = $helper;
        $this->geoIp = $geoip;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->zoneFactory = $zoneFactory;
    }

    /**
     * Get current Zone Id
     *
     * @return int|null
     */
    public function getZoneId()
    {
        $zone = $this->getZone();

        return $zone ? $zone->getId() : null;
    }

    /**
     * Get current Zone Name
     *
     * @return string|null
     */
    public function getZoneName()
    {
        $zone = $this->getZone();

        return $zone ? $zone->getName() : null;
    }

    /**
     * Get array of the regions by country_id (used as key)
     *
     * @return array
     */
    public function getRegionJsonList()
    {
        $collectionByCountry = [];
        /** @var \Magento\Directory\Model\ResourceModel\Region\Collection $collection */
        $collection = $this->regionCollectionFactory->create();
        /** @var \Magento\Directory\Model\Region $item */
        foreach ($collection as $item) {
            $collectionByCountry[$item->getData('country_id')][] = $item->getData();
        }

        return $collectionByCountry;
    }

    /**
     * Get visitors country id
     *
     * @return int
     */
    public function getCountryId()
    {
        return $this->getShippingAddress()->getCountryId();
    }

    /**
     * Get visitors region id
     *
     * @return string
     */
    public function getRegionId()
    {
        return $this->getShippingAddress()->getRegionId();
    }

    /**
     * Get visitors region (as string)
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getShippingAddress()->getRegion();
    }

    /**
     * Get visitors country name
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->getShippingAddress()->getCountryModel()->getName($this->localeResolver->getLocale());
    }

    /**
     * Get visitors region code
     *
     * @return string
     */
    public function getRegionCode()
    {
        return $this->getShippingAddress()->getRegionCode();
    }

    /**
     * Get current zone
     *
     * @return \MageWorx\ShippingRules\Model\Zone
     */
    private function getZone()
    {
        if (!$this->zone) {
            /** @var \MageWorx\ShippingRules\Model\Zone $zoneModel */
            $zoneModel = $this->zoneFactory->create();
            /** @var \MageWorx\ShippingRules\Model\Zone $zone */
            $this->zone = $zoneModel->findZoneForAddress($this->getShippingAddress());
        }

        return $this->zone;
    }

    /**
     * @return \Magento\Quote\Model\Quote\Address
     */
    private function getShippingAddress()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        if (!$shippingAddress->getCountryId()) {
            $this->resolveAddressData();
        }

        return $shippingAddress;
    }

    /**
     * Resolve current address data and store it in the shipping address (without save!)
     */
    private function resolveAddressData()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $customerData = $this->geoIp->getCurrentLocation();
        if ($customerData->getCode()) {
            /** @var \Magento\Directory\Model\Country $currentCountry */
            $currentCountry = $shippingAddress
                ->getCountryModel()
                ->loadByCode($customerData->getCode());
            if (!$currentCountry) {
                return;
            }
            $shippingAddress->setCountryId($currentCountry->getId());
            $shippingAddress->setRegion($customerData->getRegion());
            $shippingAddress->setRegionCode($customerData->getRegionCode());
            $shippingAddress->setCity($customerData->getCity());
            $shippingAddress->setPostcode($customerData->getPosttalCode());
        }
    }
}
