<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Field;

use \Ess\M2eProUpdater\Block\Adminhtml\Configuration\AbstractField;

class CurrentVersion extends AbstractField
{
    //########################################

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $helper */
        $helper = $this->helperFactory->getObject('M2ePro');

        $currentVersion = $helper->getCurrentVersion();
        if (!$currentVersion) {
            $currentVersion = __('Not Installed');
            $element->setBold(true);
        }

        $element->setValue($currentVersion);
        return parent::_getElementHtml($element);
    }

    //########################################
}