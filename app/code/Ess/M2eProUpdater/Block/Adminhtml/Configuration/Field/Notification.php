<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Field;

use Ess\M2eProUpdater\Helper\Config;
use Ess\M2eProUpdater\Block\Adminhtml\Configuration\AbstractField;

class Notification extends AbstractField
{
    //########################################

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Ess\M2eProUpdater\Helper\Config $helper */
        $helper = $this->helperFactory->getObject('Config');

        $element->setValues($this->getOptionsArray());
        $element->setValue($helper->getNotificationType());

        return parent::_getElementHtml($element);
    }

    protected function getOptionsArray()
    {
        return [
            [
                'label' => __('Do Not Notify'),
                'value' => Config::NOTIFICATIONS_DISABLED
            ],
            [
                'label' => __('On each Extension Page'),
                'value' => Config::NOTIFICATIONS_EXTENSION_PAGES
            ],
            [
                'label' => __('On each Magento Page'),
                'value' => Config::NOTIFICATIONS_MAGENTO_PAGES
            ],
            [
                'label' => __('As Magento System Notification'),
                'value' => Config::NOTIFICATIONS_MAGENTO_SYSTEM_NOTIFICATION
            ],
        ];
    }

    //########################################
}