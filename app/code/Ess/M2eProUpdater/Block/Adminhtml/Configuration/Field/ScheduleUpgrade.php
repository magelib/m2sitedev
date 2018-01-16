<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Field;

use Ess\M2eProUpdater\Helper\Config;
use Ess\M2eProUpdater\Block\Adminhtml\Configuration\AbstractField;

class ScheduleUpgrade extends AbstractField
{
    //########################################

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (!$this->isVisible()) {
            return '';
        }

        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getButtonHtml();
    }

    //########################################

    private function isVisible()
    {
        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');

        if ($configHelper->isPrepareUpgradeTaskAllowed() || $configHelper->isDoUpgradeTaskAllowed()) {
            return false;
        }

        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');

        $currentVersion = $m2eProHelper->getCurrentVersion();
        $latestAvailableVersion = $m2eProHelper->getLatestAvailableVersion();

        if ($latestAvailableVersion && version_compare($currentVersion, $latestAvailableVersion) != -1) {
            return false;
        }

        return true;
    }

    private function getButtonHtml()
    {
        $saveUrl = $this->getUrl('M2eProUpdater/configuration/save', [
            'path'  => base64_encode(Config::PREPARE_UPGRADE_TASK_ALLOWED_PATH),
            'value' => base64_encode('1')
        ]);

        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $button->setData(
            [
                'id'      => 'schedule_upgrade_button',
                'label'   => __('Confirm'),
                'class'   => 'primary',
                'onclick' => "setLocation('" . $saveUrl . "')",
            ]
        );

        return $button->toHtml();
    }

    //########################################
}