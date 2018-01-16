<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Module extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    const IDENTIFIER = 'Ess_M2eProUpdater';

    /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    private $directoryWriterFactory;

    /** @var \Magento\Framework\Module\PackageInfo */
    private $packageInfo;

    //########################################

    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Magento\Framework\Module\PackageInfo $packageInfo,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($helperFactory, $context);

        $this->directoryList = $directoryList;
        $this->directoryWriterFactory = $directoryWriterFactory;
        $this->packageInfo = $packageInfo;
    }

    //########################################

    public function getComposerVersion()
    {
        return $this->packageInfo->getVersion(self::IDENTIFIER);
    }

    //########################################

    public function getTemporaryDirectoryPath()
    {
        $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) .'/'. self::IDENTIFIER . '/tmp';
        $directory = $this->directoryWriterFactory->create($path);

        if (!$directory->isExist()) {
            $directory->create();
        }

        return $path;
    }

    public function getLogDirectoryPath()
    {
        $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) .'/'. self::IDENTIFIER . '/log';
        $directory = $this->directoryWriterFactory->create($path);

        if (!$directory->isExist()) {
            $directory->create();
        }

        return $path;
    }

    //########################################
}