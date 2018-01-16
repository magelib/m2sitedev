<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model\Cron;

class Runner extends \Ess\M2eProUpdater\Model\AbstractModel
{
    /* @var \Magento\Framework\Filesystem */
    private $filesystem;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);

        $this->filesystem = $filesystem;
        $this->logger     = $loggerFactory->create();
    }

    //########################################

    public function process()
    {
        /** @var \Ess\M2eProUpdater\Helper\Cron $helper */
        $helper = $this->helperFactory->getObject('Cron');

        if ($helper->isLocked()) {
            return false;
        }

        $this->initialize();
        $this->updateLastRun();

        try {

            $result = $this->processTasks();

        } catch (\Throwable $throwable) {

            $result = false;
            $this->logger->critical($throwable->__toString());

        } catch (\Exception $exception) {

            $result = false;
            $this->logger->error($exception->__toString());
        }

        $this->deInitialize();

        return $result;
    }

    //########################################

    protected function initialize()
    {
        /** @var \Ess\M2eProUpdater\Helper\Cron $helper */
        $helper = $this->helperFactory->getObject('Cron');
        $helper->lock();
    }

    protected function deInitialize()
    {
        /** @var \Ess\M2eProUpdater\Helper\Cron $helper */
        $helper = $this->helperFactory->getObject('Cron');
        $helper->unlock();
    }

    protected function updateLastRun()
    {
        /** @var \Ess\M2eProUpdater\Helper\Cron $helper */
        $helper = $this->helperFactory->getObject('Cron');
        $helper->setLastRun();
    }

    //########################################

    protected function processTasks()
    {
        $result = true;

        foreach ($this->getTasksList() as $taskNick) {

            try {

                /** @var \Ess\M2eProUpdater\Model\Cron\Task\AbstractModel $task */
                $task = $this->getTaskObject($taskNick);
                $tempResult = $task->process();

                if (!is_null($tempResult) && !$tempResult) {
                    $result = false;
                }

            } catch (\Exception $exception) {

                $result = false;
                $this->logger->error($exception->__toString());
            }
        }

        return $result;
    }

    //########################################

    protected function getTasksList()
    {
        return array(
            Task\PrepareUpgrade::NICK,
            Task\DoUpgrade::NICK,
        );
    }

    protected function getTaskObject($taskNick)
    {
        $taskNick = str_replace('_', ' ', $taskNick);
        $taskNick = str_replace(' ', '', ucwords($taskNick));

        /** @var $task \Ess\M2eProUpdater\Model\Cron\Task\AbstractModel **/
        $task = $this->modelFactory->getObject('Cron\Task\\'.trim($taskNick));

        return $task;
    }

    //########################################
}