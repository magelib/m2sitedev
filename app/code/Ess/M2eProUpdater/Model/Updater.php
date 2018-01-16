<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Updater extends \Ess\M2eProUpdater\Model\AbstractModel
{
    /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    private $directoryWriterFactory;

    /** @var \Ess\M2eProUpdater\Model\Git $gitRepository */
    protected $gitRepository;

    /** @var \Exception|null */
    protected $exception;

    //########################################

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Ess\M2eProUpdater\Model\Git $gitRepository,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->directoryList = $directoryList;
        $this->directoryWriterFactory = $directoryWriterFactory;
        $this->gitRepository = $gitRepository;
    }

    //########################################

    public function validate()
    {
        try {

            $this->_checkPermissions();

        } catch (\Exception $exception) {

            $this->exception = $exception;
            return false;
        }

        return true;
    }

    public function prepareNewPackage()
    {
        try {

            $this->_clearUnpackedDirectory();

            $this->_download();
            $this->_unpack();

        } catch (\Exception $exception) {

            $this->exception = $exception;
            return false;
        }

        return true;
    }

    public function updatePackage()
    {
        $this->_clearTargetDirectory();
        $this->_copy();
        $this->_clearUnpackedDirectory();
    }

    //########################################

    protected function _checkPermissions()
    {
        $path = $this->directoryList->getPath(DirectoryList::APP) . '/code/Ess/';
        $directory = $this->directoryWriterFactory->create($path);

        $directory->writeFile('permissions_check.txt', '1');
        $directory->delete('permissions_check.txt');
    }

    protected function _clearTargetDirectory()
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');
        $path = $m2eProHelper->getCodeDirectoryPath();

        $directory = $this->directoryWriterFactory->create($path);
        $directory->delete();
    }

    protected function _clearUnpackedDirectory()
    {
        $path = $this->getUnpackedDirectoryPath();
        $directory = $this->directoryWriterFactory->create($path);
        $directory->delete();
    }

    //########################################

    protected function _download()
    {
       $this->gitRepository->downloadZipPackage();
    }

    protected function _unpack()
    {
        $zipObject = new \ZipArchive();
        $zipObject->open($this->getTemporaryFileName());

        $zipObject->extractTo($this->getUnpackedDirectoryPath());
        $zipObject->close();
    }

    protected function _copy()
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eHelper */
        $m2eHelper = $this->helperFactory->getObject('M2ePro');

        $targetPath = $m2eHelper->getCodeDirectoryPath();
        $targetDirectory = $this->directoryWriterFactory->create($targetPath);

        if (!$targetDirectory->isExist()) {
            $targetDirectory->create();
        }

        $sourcePath = $this->getUnpackedDirectoryPath() . Git::PACKAGE_NAME . '-master';
        $sourceDirectory = $this->directoryWriterFactory->create($sourcePath);

        if (!$sourceDirectory->isExist()) {
            throw new \Exception("Package '{$sourcePath}' is not found.");
        }

        foreach ($sourceDirectory->readRecursively() as $path) {

            if (!$sourceDirectory->getDriver()->isFile($sourceDirectory->getAbsolutePath($path))) {
                continue;
            }

            $sourceDirectory->copyFile($path, $path, $targetDirectory);
        }

        $sourceDirectory->delete();
    }

    //########################################

    protected function getUnpackedDirectoryPath()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');
        $path = $moduleHelper->getTemporaryDirectoryPath() . '/unpacked/';

        $directory = $this->directoryWriterFactory->create($path);
        if (!$directory->isExist()) {
            $directory->create();
        }

        return $path;
    }

    protected function getTemporaryFileName()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');
        return $moduleHelper->getTemporaryDirectoryPath() .'/'. Git::PACKAGE_NAME . '.zip';
    }

    //########################################

    public function getException()
    {
        return $this->exception;
    }

    //########################################
}