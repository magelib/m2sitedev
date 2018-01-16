<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Config\Source\Locale;

use Magento\Framework\Data\OptionSourceInterface;

class Country extends \Magento\Config\Model\Config\Source\Locale\Country implements OptionSourceInterface
{
    const CODE_WORLD = '001';

    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $options[] = ['label' => __('Select Country'), 'value' => ''];
        $origCountryOptions = parent::toOptionArray();
        $options = array_merge($options, $origCountryOptions);
        $this->options = $options;

        return $this->options;
    }
}
