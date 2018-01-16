<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model;

class LoggerFactory extends \Ess\M2eProUpdater\Model\AbstractModel
{
    const LOGFILE_NAME = 'cron-error.log';

    /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
    private $directoryList;

    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    //########################################

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        $data = []
    ) {
        $this->directoryList = $directoryList;
        $this->objectManager = $objectManager;
        parent::__construct($helperFactory, $modelFactory, $data);
    }

    //########################################

    public function create($channelName = 'm2epro-updater-log',
                           $fileName = self::LOGFILE_NAME,
                           array $data = [])
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $helper */
        $helper = $this->helperFactory->getObject('Module');
        $logFilePath = $helper->getLogDirectoryPath() .'/'. $fileName;

        $streamHandler = new \Monolog\Handler\StreamHandler($logFilePath);
        $streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter());

        $logger = $this->objectManager->create(
            'Magento\Framework\Logger\Monolog', [
                'name'     => $channelName,
                'handlers' => [$streamHandler]
            ]
        );

        return $logger;
    }

    //########################################
}