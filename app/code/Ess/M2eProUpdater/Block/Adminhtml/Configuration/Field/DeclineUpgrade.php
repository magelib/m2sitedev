<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Field;

use Ess\M2eProUpdater\Helper\Config;
use Ess\M2eProUpdater\Block\Adminhtml\Configuration\AbstractField;

class DeclineUpgrade extends AbstractField
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
        /** @var \Ess\M2eProUpdater\Helper\Config $helper */
        $helper = $this->helperFactory->getObject('Config');
        return $helper->isPrepareUpgradeTaskAllowed();
    }

    private function getButtonHtml()
    {
        $saveUrl = $this->getUrl('M2eProUpdater/configuration/save', [
            'path'  => base64_encode(Config::PREPARE_UPGRADE_TASK_ALLOWED_PATH),
            'value' => base64_encode('0')
        ]);

        $button = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $button->setData(
            [
                'id'      => 'decline_upgrade_button',
                'label'   => __('Confirm'),
                'class'   => 'primary',
                'onclick' => "setLocation('" . $saveUrl . "')",
            ]
        );

        return $button->toHtml();
    }

    //########################################
}