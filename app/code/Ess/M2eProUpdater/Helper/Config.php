<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper;

class Config extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    const NOTIFICATIONS_PATH = 'm2epro_updater/notifications';

    const PREPARE_UPGRADE_TASK_ALLOWED_PATH = 'm2epro_updater/prepare_upgrade_task_allowed';
    const DO_UPGRADE_TASK_ALLOWED_PATH      = 'm2epro_updater/do_upgrade_task_allowed';

    const NOTIFICATIONS_DISABLED                    = 0;
    const NOTIFICATIONS_EXTENSION_PAGES             = 1;
    const NOTIFICATIONS_MAGENTO_PAGES               = 2;
    const NOTIFICATIONS_MAGENTO_SYSTEM_NOTIFICATION = 3;

    //########################################

    public function isPrepareUpgradeTaskAllowed()
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        return (bool)(int)$helper->getValue(self::PREPARE_UPGRADE_TASK_ALLOWED_PATH);
    }

    public function setPrepareUpgradeTaskAllowed($value)
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        $helper->setValue(self::PREPARE_UPGRADE_TASK_ALLOWED_PATH, (int)$value);
    }

    //----------------------------------------

    public function isDoUpgradeTaskAllowed()
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        return (bool)(int)$helper->getValue(self::DO_UPGRADE_TASK_ALLOWED_PATH);
    }

    public function setDoUpgradeTaskAllowed($value)
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        $helper->setValue(self::DO_UPGRADE_TASK_ALLOWED_PATH, (int)$value);
    }

    //----------------------------------------

    public function getNotificationType()
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        return (int)$helper->getValue(self::NOTIFICATIONS_PATH, self::NOTIFICATIONS_EXTENSION_PAGES);
    }

    public function setNotificationType($value)
    {
        /** @var \Ess\M2eProUpdater\Helper\Magento\Config $helper */
        $helper = $this->getHelper('Magento\Config');
        $helper->setValue(self::NOTIFICATIONS_PATH, (int)$value);
    }

    //----------------------------------------

    public function isNotificationDisabled()
    {
        return $this->getNotificationType() == self::NOTIFICATIONS_DISABLED;
    }

    public function isNotificationExtensionPages()
    {
        return $this->getNotificationType() == self::NOTIFICATIONS_EXTENSION_PAGES;
    }

    public function isNotificationMagentoPages()
    {
        return $this->getNotificationType() == self::NOTIFICATIONS_MAGENTO_PAGES;
    }

    public function isNotificationMagentoSystemNotification()
    {
        return $this->getNotificationType() == self::NOTIFICATIONS_MAGENTO_SYSTEM_NOTIFICATION;
    }

    //########################################
}