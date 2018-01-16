<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Fieldset;

use Ess\M2eProUpdater\Block\Adminhtml\Configuration\Fieldset;

class Actions extends Fieldset
{
    //########################################

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (!$this->isVisible()) {
            return '';
        }

        return parent::render($element);
    }

    protected function _getHeaderTitleHtml($element)
    {
        return parent::_getHeaderTitleHtml($element) .
               $this->getMessageHtml();
    }

    //########################################

    private function isVisible()
    {
        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');

        if ($configHelper->isPrepareUpgradeTaskAllowed() || $configHelper->isDoUpgradeTaskAllowed()) {
            return true;
        }

        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');

        $currentVersion = $m2eProHelper->getCurrentVersion();
        $latestAvailableVersion = $m2eProHelper->getLatestAvailableVersion(false);

        if ($latestAvailableVersion && version_compare($currentVersion, $latestAvailableVersion) == -1) {
            return true;
        }

        return false;
    }

    private function getMessageHtml()
    {
        /** @var \Ess\M2eProUpdater\Helper\Config $helper */
        $helper = $this->helperFactory->getObject('Config');

        if (!$helper->isPrepareUpgradeTaskAllowed() && !$helper->isDoUpgradeTaskAllowed()) {
           return '';
        }

        /** @var \Magento\Framework\View\Element\Messages $messagesBlock */
        $messagesBlock = $this->getLayout()->createBlock('Magento\Framework\View\Element\Messages');
        $messagesBlock->addNotice(__(
            'The M2E Pro Module upgrade was planned and will be executed in the nearest time.'
        ));

        return $messagesBlock->toHtml();
    }

    //########################################
}