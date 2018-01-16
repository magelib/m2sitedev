<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Fblike\Block\Product;

use Magento\Framework\View\Element\Template;

class Button extends Template
{
    /**
     * @param Template\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $registry,
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->_coreRegistry = $registry;
        $this->_product = $this->_coreRegistry->registry('product');
        parent::__construct($context, $data);
    }

    public function getProductUrl()
    {
        return $this->_product->getProductUrl();
    }

    public function getLikeButtonConfig()
    {
        return $this->_scopeConfig->getValue("fblike/product");
    }

    public function getFacebookAppId()
    {
        return $this->_scopeConfig->getValue("fblike/general/app");
    }

    public function getButtonLocale()
    {
        return $this->_scopeConfig->getValue("fblike/general/locale");
    }

    public function canShowInCategory()
    {
        return $this->_scopeConfig->getValue("fblike/category/enabled");
    }
}
