<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Plugin\Front\Controller;

use Ess\M2eProUpdater\Helper\Config;
use Magento\Framework\Message\MessageInterface;
use Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as ConfigStructure;

class Notifications extends \Ess\M2eProUpdater\Plugin\AbstractPlugin
{
    const NOTIFICATION_MESSAGE_IDENTIFIER = 'm2epro_updater_message';

    /** @var \Ess\M2eProUpdater\Model\Git $gitRepository */
    protected $gitRepository;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlBuilder;

    /** @var \Magento\Backend\Model\Auth */
    protected $auth;

    /** @var \Ess\M2eProUpdater\Helper\Config */
    protected $configHelper;

    /** @var \Ess\M2eProUpdater\Helper\M2ePro */
    protected $m2eProHelper;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Ess\M2eProUpdater\Model\Git $gitRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Backend\Model\Auth $auth
    ) {
        $this->gitRepository  = $gitRepository;
        $this->messageManager = $messageManager;
        $this->urlBuilder     = $urlBuilder;
        $this->auth           = $auth;

        parent::__construct($helperFactory, $modelFactory);

        $this->configHelper = $this->helperFactory->getObject('Config');
        $this->m2eProHelper = $this->helperFactory->getObject('M2ePro');
    }

    //########################################

    public function aroundDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ){
        if ($this->shouldBeSkipped($request)) {
            return $proceed($request);
        }

        $notificationsSetting = $this->configHelper->getNotificationType();

        if ($notificationsSetting == Config::NOTIFICATIONS_DISABLED ||
            $notificationsSetting == Config::NOTIFICATIONS_MAGENTO_SYSTEM_NOTIFICATION) {

            return $proceed($request);
        }

        $currentVersion = $this->m2eProHelper->getCurrentVersion();
        $latestVersion  = $this->m2eProHelper->getLatestAvailableVersion();

        if ($currentVersion && version_compare($currentVersion, $latestVersion) == -1) {

            $link = $this->urlBuilder->getUrl('adminhtml/system_config/edit', [
                'section' => ConfigStructure::INSTALLATION_UPGRADE_SECTION
            ]);

            $message = <<<HTML
The new version {$latestVersion} of Multi-Channels Integration (M2E Pro Module) is available for upgrade. 
To run the Module upgrade, please, follow this <a target="_blank" href="{$link}">link</a>
HTML;
            $this->messageManager->addMessage(
                $this->messageManager->createMessage(MessageInterface::TYPE_WARNING,
                                                     self::NOTIFICATION_MESSAGE_IDENTIFIER)
                     ->setText(__($message))
            );

        }

        return $proceed($request);
    }

    //########################################

    private function shouldBeSkipped(\Magento\Framework\App\RequestInterface $request)
    {
        /** @var \Magento\Framework\App\Request\Http $request */

        if (!$this->auth->isLoggedIn() || $request->isPost() || $request->isAjax()) {
            return true;
        }

        if ($this->configHelper->isPrepareUpgradeTaskAllowed() ||
            $this->configHelper->isDoUpgradeTaskAllowed()) {
            return true;
        }

        if ($this->configHelper->isNotificationExtensionPages() &&
            strpos($request->getPathInfo(), 'm2epro') === false) {
            return true;
        }

        // do not show on configuration page
        if (strpos($request->getPathInfo(), 'system_config/edit') !== false &&
            strpos($request->getPathInfo(), 'section/'.ConfigStructure::INSTALLATION_UPGRADE_SECTION) !== false) {
            return true;
        }

        // do not show on save configuration page
        if (strpos($request->getPathInfo(), 'configuration/save') !== false) {
            return true;
        }

        // after redirect message can be added twice
        foreach ($this->messageManager->getMessages()->getItems() as $message) {
            if ($message->getIdentifier() == self::NOTIFICATION_MESSAGE_IDENTIFIER) {
                return true;
            }
        }

        return false;
    }

    //########################################
}