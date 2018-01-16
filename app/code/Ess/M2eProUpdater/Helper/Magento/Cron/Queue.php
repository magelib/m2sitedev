<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper\Magento\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\Cron\Queue as SetupQueue;

class Queue extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    const FILE_NAME = '.update_queue.json';

    /* @var \Magento\Framework\Filesystem */
    private $filesystem;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ){
        $this->filesystem = $filesystem;

        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function getJobs()
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        if (!$directory->isExist(self::FILE_NAME)) {
            return [];
        }

        $jobs = (array)json_decode($directory->readFile(self::FILE_NAME), true);
        return $jobs;
    }

    public function addJob($jobName, array $jobParams = [])
    {
        $jobs = $this->getJobs();
        $jobs[SetupQueue::KEY_JOBS][] = ['name' => $jobName, 'params' => $jobParams];

        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directory->writeFile(self::FILE_NAME, json_encode($jobs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    //########################################
}