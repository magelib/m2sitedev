<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration;

abstract class AbstractField extends \Magento\Config\Block\System\Config\Form\Field
{
    /** @var \Ess\M2eProUpdater\Helper\Factory */
    protected $helperFactory;

    /** @var \Ess\M2eProUpdater\Model\Factory */
    protected $modelFactory;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperFactory = $helperFactory;
        $this->modelFactory  = $modelFactory;
    }

    //########################################

    protected function _isInheritCheckboxRequired(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return false;
    }

    protected function _renderScopeLabel(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '';
    }

    //########################################
}