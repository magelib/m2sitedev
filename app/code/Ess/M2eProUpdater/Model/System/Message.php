<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model\System;

use \Magento\Framework\Notification\MessageInterface;
use \Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as Structure;

class Message extends \Ess\M2eProUpdater\Model\AbstractModel implements MessageInterface
{
    //########################################

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /** @var \Magento\Backend\Model\Auth */
    protected $authorization;

    protected $currentVersion;
    protected $latestVersion;

    //########################################

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Backend\Model\Auth $authorization,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);
        $this->urlBuilder    = $urlBuilder;
        $this->authorization = $authorization;

        /** @var \Ess\M2eProUpdater\Helper\M2ePro $helper */
        $helper = $this->helperFactory->getObject('M2ePro');

        $this->currentVersion = $helper->getCurrentVersion();
        $this->latestVersion  = $helper->getLatestAvailableVersion();
    }

    //########################################

    public function getIdentity()
    {
        return md5('m2epro-latest-version-' . $this->latestVersion);
    }

    public function isDisplayed()
    {
        if (!$this->authorization->isLoggedIn()) {
            return false;
        }

        /** @var \Ess\M2eProUpdater\Helper\Config $helper */
        $helper = $this->helperFactory->getObject('Config');

        if (!$helper->isNotificationMagentoSystemNotification()) {
            return false;
        }

        if (!$this->currentVersion || version_compare($this->currentVersion, $this->latestVersion) != -1) {
            return false;
        }

        return true;
    }

    public function getText()
    {
        $link = $this->urlBuilder->getUrl('adminhtml/system_config/edit', [
            'section' => Structure::INSTALLATION_UPGRADE_SECTION
        ]);

        $message = <<<HTML
The new version {$this->latestVersion} of Multi-Channels Integration (M2E Pro Module) is available for upgrade. 
To run the Module upgrade, please, follow this <a target="_blank" href="{$link}">link</a>
HTML;

        return __($message);
    }

    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }

    //########################################
}