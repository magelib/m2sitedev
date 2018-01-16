<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use MageWorx\ShippingRules\Api\AddressResolverInterface;

class CheckoutData implements SectionSourceInterface
{
    /**
     * @var AddressResolverInterface
     */
    protected $addressResolver;

    /**
     * Location constructor.
     * @param AddressResolverInterface $addressResolver
     */
    public function __construct(
        AddressResolverInterface $addressResolver
    ) {
        $this->addressResolver = $addressResolver;
    }

    /**
     * Get data for the checkout-data section:
     * country_id, region, region_id of the shipping & billing addresses
     * Used during checkout to fill address fields with default values based on the customers selection (popup) or
     * on the geoIp location.
     */
    public function getSectionData()
    {
        return [
            'shippingAddressFromData' => [
                'country_id' => $this->addressResolver->getCountryId(),
                'region' => $this->addressResolver->getRegion(),
                'region_id' => $this->addressResolver->getRegionId(),
            ],
            'billingAddressFormData' => [
                'country_id' => $this->addressResolver->getCountryId(),
                'region' => $this->addressResolver->getRegion(),
                'region_id' => $this->addressResolver->getRegionId(),
            ],
        ];
    }
}
