<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model\Cron\Task;

final class DoUpgrade extends AbstractModel
{
    const NICK = 'doUpgrade';

    /** @var \Ess\M2eProUpdater\Model\Updater $updater */
    protected $updater;

    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    /** @var \Magento\Framework\App\MaintenanceMode $maintenance */
    protected $maintenance;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Model\Updater $updater,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory,
        $data = []
    ) {
        parent::__construct($directoryWriterFactory, $helperFactory, $modelFactory, $loggerFactory, $data);

        $this->updater = $updater;
        $this->objectManager = $objectManager;

        $this->maintenance = $this->objectManager->get('Magento\Framework\App\MaintenanceMode');
    }

    //########################################

    protected function performActions()
    {
        try {

            $this->updater->updatePackage();

            /** @var \Ess\M2eProUpdater\Helper\Magento\Cron\Queue $queueHelper */
            $queueHelper = $this->helperFactory->getObject('Magento\Cron\Queue');
            $queueHelper->addJob(\Magento\Setup\Model\Cron\JobFactory::JOB_UPGRADE);

        } catch (\Throwable $throwable) {
            $this->logger->critical($throwable->__toString());
        } catch (\Exception $exception) {
            $this->logger->error($exception->__toString());
        }

        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');
        $configHelper->setDoUpgradeTaskAllowed('0');

        return true;
    }

    protected function isPossibleToRun()
    {
        if (!parent::isPossibleToRun()) {
            return false;
        }

        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');
        if (!$configHelper->isDoUpgradeTaskAllowed()) {
            return false;
        }

        if (!$this->maintenance->isOn()) {
            return false;
        }

        return true;
    }

    //########################################
}