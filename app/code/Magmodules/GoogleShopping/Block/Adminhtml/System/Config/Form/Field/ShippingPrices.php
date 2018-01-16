<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magmodules\GoogleShopping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\DataObject;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ShippingPrices extends AbstractFieldArray
{

    protected $columns = [];
    protected $countryRenderer;

    /**
     * Render block
     */
    protected function _prepareToRender()
    {
        $this->addColumn('code', [
            'label' => __('Country'),
            'renderer' => $this->getCountryRenderer()
        ]);
        $this->addColumn('service', [
            'label' => __('Service'),
        ]);
        $this->addColumn('price_from', [
            'label' => __('From'),
        ]);
        $this->addColumn('price_to', [
            'label' => __('To'),
        ]);
        $this->addColumn('price', [
            'label' => __('Price'),
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Returns render of countries
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    protected function getCountryRenderer()
    {
        if (!$this->countryRenderer) {
            $this->countryRenderer = $this->getLayout()->createBlock(
                '\Magmodules\GoogleShopping\Block\Adminhtml\System\Config\Form\Field\Renderer\Countries',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->countryRenderer;
    }

    /**
     * Prepare existing row data object
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $attribute = $row->getCode();
        $options = [];
        if ($attribute) {
            $options['option_' . $this->getCountryRenderer()->calcOptionHash($attribute)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
