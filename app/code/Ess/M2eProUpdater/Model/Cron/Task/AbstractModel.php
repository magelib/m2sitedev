<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model\Cron\Task;

abstract class AbstractModel extends \Ess\M2eProUpdater\Model\AbstractModel
{
    const LOG_FILE_NAME_MASK = 'upgrade-to-version-%ver%.log';

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    private $directoryWriterFactory;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    protected $logFileName;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory,
        $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->directoryWriterFactory = $directoryWriterFactory;
        $this->initializeLogger($loggerFactory);
    }

    //########################################

    public function process()
    {
        if (!$this->isPossibleToRun()) {
            return true;
        }

        $tempResult = $this->performActions();

        if (!is_null($tempResult) && !$tempResult) {
            $tempResult = false;
        }

        return $tempResult;
    }

    // ---------------------------------------

    protected function isPossibleToRun()
    {
        return true;
    }

    // ---------------------------------------

    abstract protected function performActions();

    //########################################

    protected function initializeLogger(\Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory)
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');
        $latestVersion = $m2eProHelper->getLatestAvailableVersion();

        $fileName = self::LOG_FILE_NAME_MASK;
        $latestVersion && $fileName = str_replace('%ver%', $latestVersion, $fileName);

        $this->logger      = $loggerFactory->create('m2epro-updater-log', $fileName);
        $this->logFileName = $fileName;
    }

    protected function clearPreviousLogs()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');

        $directory = $this->directoryWriterFactory->create($moduleHelper->getLogDirectoryPath());
        $directory->delete($this->logFileName);
    }

    //########################################
}