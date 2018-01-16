<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magmodules\GoogleShopping\Block\Adminhtml\System\Config\Form\Field\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Magento\Directory\Model\Config\Source\Country as CountrySource;

class Countries extends Select
{

    protected $country = [];
    protected $countries;

    /**
     * Countries constructor.
     * @param Context $context
     * @param CountrySource $countries
     * @param array $data
     */
    public function __construct(
        Context $context,
        CountrySource $countries,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countries = $countries;
    }

    /**
     * Render block HTML
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getCountrySource() as $country) {
                $this->addOption($country['value'], $country['label']);
            }
        }

        return parent::_toHtml();
    }

    /**
     * Get all countries
     * @return array
     */
    protected function getCountrySource()
    {
        if (!$this->country) {
            $this->country = $this->countries->toOptionArray();
        }

        return $this->country;
    }

    /**
     * Sets name for input element
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
