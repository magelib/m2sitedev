<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper;

use \Magento\Framework\App\Filesystem\DirectoryList;

class Cron extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    const STATUS_FILE_NAME = '.cron_status';
    const LOCK_FILE_NAME   = '.cron_lock';

    const LOCK_FILE_LIFETIME = 600;

    /* @var \Magento\Framework\Filesystem */
    private $filesystem;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory */
    private $directoryReaderFactory;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    private $directoryWriterFactory;

    /** @var  \Magento\Framework\Stdlib\DateTime\DateTime */
    private $localeDate;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryReaderFactory,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $localeDate,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ){
        $this->filesystem    = $filesystem;
        $this->directoryList = $directoryList;
        $this->directoryReaderFactory = $directoryReaderFactory;
        $this->directoryWriterFactory = $directoryWriterFactory;
        $this->localeDate    = $localeDate;

        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function isInstalled()
    {
        return $this->getLastRun() !== null;
    }

    public function isLastRunMoreThan($interval, $isHours = false)
    {
        $isHours && $interval *= 3600;

        $lastRun = $this->getLastRun();
        if (is_null($lastRun)) {
            return false;
        }

        return (int)$this->localeDate->gmtTimestamp() > strtotime($lastRun) + $interval;
    }

    //########################################

    public function getLastRun()
    {
        $directory = $this->directoryReaderFactory->create($this->getLockDirectoryPath());

        if ($directory->isExist(self::STATUS_FILE_NAME)) {
            return $directory->readFile(self::STATUS_FILE_NAME);
        }

        return null;
    }

    public function setLastRun($value = null)
    {
        if (is_null($value)) {
            $value = new \DateTime('now', new \DateTimeZone('UTC'));
            $value = $value->format('Y-m-d H:i:s');
        }

        $directory = $this->directoryWriterFactory->create($this->getLockDirectoryPath());
        $directory->writeFile(self::STATUS_FILE_NAME, $value);
    }

    //########################################

    public function lock()
    {
        $directory = $this->directoryWriterFactory->create($this->getLockDirectoryPath());
        $directory->writeFile(self::LOCK_FILE_NAME, getmypid());
    }

    public function unlock()
    {
        $directory = $this->directoryWriterFactory->create($this->getLockDirectoryPath());
        $directory->delete(self::LOCK_FILE_NAME);
    }

    public function isLocked()
    {
        $directory = $this->directoryWriterFactory->create($this->getLockDirectoryPath());

        if (!$directory->isExist(self::LOCK_FILE_NAME)) {
            return false;
        }

        $mTime = filemtime($this->getLockDirectoryPath() .'/'. self::LOCK_FILE_NAME);
        if ((int)$this->localeDate->gmtTimestamp() > $mTime + self::LOCK_FILE_LIFETIME) {

            $directory->delete(self::LOCK_FILE_NAME);
            return false;
        }

        return true;
    }

    //########################################

    protected function getLockDirectoryPath()
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) .'/'.
               \Ess\M2eProUpdater\Helper\Module::IDENTIFIER;
    }

    //########################################
}