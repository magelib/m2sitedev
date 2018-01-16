<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration;

class Fieldset extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /** @var \Ess\M2eProUpdater\Helper\Factory */
    protected $helperFactory;

    /** @var \Ess\M2eProUpdater\Model\Factory */
    protected $modelFactory;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->helperFactory = $helperFactory;
        $this->modelFactory  = $modelFactory;
    }

    //########################################

    protected function _getHeaderTitleHtml($element)
    {
        return '<a id="' .
                $element->getHtmlId() .
                '-head" href="#' .
                $element->getHtmlId() .
                '-link" onclick="return false;">' . $element->getLegend() . '</a>';
    }

    protected function _isCollapseState($element)
    {
        return true;
    }

    //########################################
}