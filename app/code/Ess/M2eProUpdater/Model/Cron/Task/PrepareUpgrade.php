<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model\Cron\Task;

final class PrepareUpgrade extends AbstractModel
{
    const NICK = 'prepareUpgrade';

    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    /** @var \Ess\M2eProUpdater\Model\Updater $updater */
    protected $updater;

    protected $currentVersion;
    protected $latestVersion;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ess\M2eProUpdater\Model\Updater $updater,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory,
        $data = []
    ) {
        parent::__construct($directoryWriterFactory, $helperFactory, $modelFactory, $loggerFactory, $data);

        $this->objectManager = $objectManager;
        $this->updater = $updater;

        /** @var \Ess\M2eProUpdater\Helper\M2ePro $helper */
        $helper = $this->helperFactory->getObject('M2ePro');

        $this->currentVersion = $helper->getCurrentVersion();
        $this->latestVersion  = $helper->getLatestAvailableVersion();
    }

    //########################################

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');
        if (!$configHelper->isPrepareUpgradeTaskAllowed()) {
            return false;
        }

        if (version_compare($this->currentVersion, $this->latestVersion) !== -1) {
            return false;
        }

        return true;
    }

    protected function performActions()
    {
        $this->clearPreviousLogs();

        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');
        $configHelper->setPrepareUpgradeTaskAllowed('0');

        if (!$this->updater->validate() ||
            !$this->updater->prepareNewPackage()) {

            $this->logger->error($this->updater->getException()->__toString());
            return true;
        }

        $configHelper->setDoUpgradeTaskAllowed('1');

        /** @var \Ess\M2eProUpdater\Helper\Magento\Cron\Queue $queueHelper */
        $queueHelper = $this->helperFactory->getObject('Magento\Cron\Queue');
        $queueHelper->addJob(\Magento\Setup\Model\Updater::TASK_TYPE_MAINTENANCE_MODE, ['enable' => true]);

        return true;
    }

    //########################################
}