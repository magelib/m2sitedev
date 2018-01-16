<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class M2ePro extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    const IDENTIFIER = 'Ess_M2ePro';

    const LATEST_VERSION_CACHE_KEY = '_latest_version_key_';

    /** @var \Magento\Framework\Module\FullModuleList */
    protected $fullModuleList;

    /** @var \Magento\Framework\Module\ModuleList */
    protected $moduleList;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
    protected $directoryList;

    /** @var \Ess\M2eProUpdater\Model\Git $gitRepository */
    protected $gitRepository;

    /** @var \Magento\Framework\ObjectManagerInterface  */
    protected $objectManager;

    //########################################

    public function __construct(
        \Magento\Framework\Module\FullModuleList $fullModuleList,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Git $gitRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Helper\Context $context
    ){
        $this->fullModuleList = $fullModuleList;
        $this->moduleList     = $moduleList;
        $this->directoryList  = $directoryList;
        $this->gitRepository  = $gitRepository;
        $this->objectManager  = $objectManager;

        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function isInstalled()
    {
        return $this->fullModuleList->getOne(self::IDENTIFIER);
    }

    public function isEnabled()
    {
        return $this->moduleList->getOne(self::IDENTIFIER);
    }

    //----------------------------------------

    public function getCurrentVersion()
    {
        if (!$this->isInstalled()) {
            return null;
        }

        /** @var \Ess\M2ePro\Helper\Module $helper */
        $helper = $this->objectManager->get('Ess\M2ePro\Helper\Module');
        return $helper->getPublicVersion();
    }

    public function getLatestAvailableVersion($useCache = true)
    {
        /** @var \Ess\M2eProUpdater\Helper\Data\Cache\Permanent $helper */
        $helper = $this->helperFactory->getObject('Data\Cache\Permanent');

        $value = null;
        $useCache && $value = $helper->getValue(self::LATEST_VERSION_CACHE_KEY);
        !$value   && $value = $this->gitRepository->getLatestAvailableVersion();

        $value && $helper->setValue(self::LATEST_VERSION_CACHE_KEY, $value, [], 60*60*12);
        return $value;
    }

    //########################################

    public function getCodeDirectoryPath()
    {
        return $this->directoryList->getPath(DirectoryList::APP) . '/code/Ess/M2ePro/';
    }

    //########################################
}