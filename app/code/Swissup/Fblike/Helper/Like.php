<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\Fblike\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Like extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_scopeConfig = $context->getScopeConfig();

        parent::__construct($context);
    }

    public function getProductLike($product)
    {
        $config = $this->_scopeConfig->getValue("fblike/category");
        $layout = $config["layout"];
        $action = $config["action"];
        $colorscheme = $config["colorscheme"];
        $share = $config["share"];
        $show_faces = $config["show_faces"];
        if ($config["enabled"]) {
            return '
            <div class="fb-like" data-href="' . $product->getProductUrl() . '"
                data-layout="' . $layout . '"
                data-action="' . $action . '"
                data-colorscheme="' . $colorscheme . '"
                data-share="' . $share . '"
                data-show-faces="' . $show_faces . '">
            </div>';
        }

        return '';
    }
}
