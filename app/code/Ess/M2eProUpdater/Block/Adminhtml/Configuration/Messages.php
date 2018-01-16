<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Setup\Model\Cron\ReadinessCheck;
use Magento\Setup\Model\CronScriptReadinessCheck;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Ess\M2eProUpdater\Model\Cron\Task\AbstractModel;
use Ess\M2eProUpdater\Block\Adminhtml\AbstractBlock;
use Ess\M2eProUpdater\Model\LoggerFactory;
use Magento\Framework\View\Element;

class Messages extends AbstractBlock implements RendererInterface
{
    /** @var \Magento\Framework\Filesystem $fileSystem */
    protected $fileSystem;

    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory */
    protected $directoryReaderFactory;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    private $localeDate;

    //########################################

    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryReaderFactory,
        \Ess\M2eProUpdater\Model\LoggerFactory $loggerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $localeDate,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        $this->directoryReaderFactory = $directoryReaderFactory;
        $this->fileSystem             = $context->getFilesystem();
        $this->localeDate             = $localeDate;

        parent::__construct($helperFactory, $modelFactory, $context, $data);
    }

    //########################################

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    //########################################

    protected function _toHtml()
    {
        /** @var \Magento\Framework\View\Element\Messages $messagesBlock */
        $messagesBlock = $this->getLayout()->createBlock('Magento\Framework\View\Element\Messages');

        $this->addOwnCronMessages($messagesBlock);

        $this->addMagentoCronMessages($messagesBlock);
        $this->addMagentoUpdateCronMessages($messagesBlock);

        $this->addGlobalCronErrorMessage($messagesBlock);
        $this->addLatestUpgradeWasCompletedUnsuccessfullyMessage($messagesBlock);

        return $messagesBlock->toHtml();
    }

    //########################################

    private function addOwnCronMessages(Element\Messages $messagesBlock)
    {
        /** @var \Ess\M2eProUpdater\Helper\Cron $helper */
        $helper = $this->helperFactory->getObject('Cron');

        if (!$helper->isInstalled()) {

            $url = 'http://docs.m2epro.com/x/bQM0AQ';
            $message = <<<HTML
Attention! The calls of the cron file <b>m2epro_updater_cron.php</b> which are required for 
correct execution of the Installation/Upgrade processes of M2E Pro Module are not configured.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addError(__($message));
            return;
        }

        if ($helper->isLastRunMoreThan(1800)) {

            $url = 'http://docs.m2epro.com/x/bQM0AQ';
            $message = <<<HTML
Attention! There were no calls of the cron file <b>m2epro_updater_cron.php</b> made during more than 30 minutes.<br>
It is not allowing correctly executing the Installation/Upgrade processes of M2E Pro Module.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addWarning(__($message));
        }
    }

    //----------------------------------------

    private function addMagentoCronMessages(Element\Messages $messagesBlock)
    {
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if (!$directory->isExist(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE)) {

            $url = 'http://devdocs.magento.com/guides/v2.0/comp-mgr/prereq/prereq_cron.html';
            $message = <<<HTML
Attention! The calls of the cron file <b>bin/magento setup:cron:run</b> are not configured that does not 
allow executing Installation/Upgrade processes of M2E Pro Module.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addError(__($message));
            return;
        }

        $lastRunStamp = filemtime($directory->getAbsolutePath(ReadinessCheck::SETUP_CRON_JOB_STATUS_FILE));

        if ($this->localeDate->gmtTimestamp() > $lastRunStamp + 1800) {

            $url = 'http://devdocs.magento.com/guides/v2.0/comp-mgr/prereq/prereq_cron.html';
            $message = <<<HTML
Attention! The calls of the cron file <b>bin/magento setup:cron:run</b> were not performed during more than 30 minutes.
<br>It does not allow executing Installation/Upgrade processes of M2E Pro Module.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addWarning(__($message));
        }
    }

    private function addMagentoUpdateCronMessages(Element\Messages $messagesBlock)
    {
        $directory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if (!$directory->isExist(CronScriptReadinessCheck::UPDATER_CRON_JOB_STATS_FILE)) {

            $url = 'http://devdocs.magento.com/guides/v2.0/comp-mgr/prereq/prereq_cron.html';
            $message = <<<HTML
Attention! The calls of the cron file <b>update/cron.php</b> are not configured that does not allow 
executing Installation/Upgrade processes of M2E Pro Module.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addError(__($message));
            return;
        }

        $lastRunStamp = filemtime($directory->getAbsolutePath(CronScriptReadinessCheck::UPDATER_CRON_JOB_STATS_FILE));

        if ($this->localeDate->gmtTimestamp() > $lastRunStamp + 1800) {

            $url = 'http://devdocs.magento.com/guides/v2.0/comp-mgr/prereq/prereq_cron.html';
            $message = <<<HTML
Attention! The calls of the cron file <b>update/cron.php</b> were not performed during more than 30 minutes.
<br>It does not allow executing Installation/Upgrade processes of M2E Pro Module.<br>
To solve this issue you should follow the instructions available in <a target="_blank" href="{$url}">the article</a>.
HTML;
            $messagesBlock->addWarning(__($message));
        }
    }

    //----------------------------------------

    private function addGlobalCronErrorMessage(Element\Messages $messagesBlock)
    {
        if ($this->isGlobalCronErrorMessages()) {

            $fileName = LoggerFactory::LOGFILE_NAME;
            $downloadUrl = $this->_urlBuilder->getUrl('M2eProUpdater/log/get', ['file_name' => $fileName]);
            $removeUrl   = $this->_urlBuilder->getUrl('M2eProUpdater/log/remove', ['file_name' => $fileName]);

            $message = <<<HTML
M2E Pro Updater error log contains some records. You can find a detailed information about the failure in the 
<a href="{$downloadUrl}">Log</a> or <a href="{$removeUrl}">Ignore this message</a>.
HTML;
            $messagesBlock->addError(__($message));
        }
    }

    private function isGlobalCronErrorMessages()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');
        $directory = $this->directoryReaderFactory->create($moduleHelper->getLogDirectoryPath());

        if (!$directory->isExist(LoggerFactory::LOGFILE_NAME)) {
            return false;
        }

        return true;
    }

    //----------------------------------------

    private function addLatestUpgradeWasCompletedUnsuccessfullyMessage(Element\Messages $messagesBlock)
    {
        if ($this->isLatestUpgradeWasCompletedUnsuccessfully()) {

            $fileName = $this->getLatestUpgradeLogFileName();
            $downloadUrl = $this->_urlBuilder->getUrl('M2eProUpdater/log/get', ['file_name' => $fileName]);
            $removeUrl   = $this->_urlBuilder->getUrl('M2eProUpdater/log/remove', ['file_name' => $fileName]);

            $message = <<<HTML
The latest attempt to Install/Upgrade the Module was unsuccessful. 
M2E Pro version was not upgraded. 
You can find a detailed information about the failure in the 
<a href="{$downloadUrl}">Log</a> or <a href="{$removeUrl}">Ignore this message</a>.
HTML;
            $messagesBlock->addError(__($message));
        }
    }

    private function isLatestUpgradeWasCompletedUnsuccessfully()
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');
        $currentVersion = $m2eProHelper->getCurrentVersion();
        $latestVersion  = $m2eProHelper->getLatestAvailableVersion();

        if (version_compare($currentVersion, $latestVersion) != -1) {
            return false;
        }

        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');

        $filename = $this->getLatestUpgradeLogFileName();
        $directory = $this->directoryReaderFactory->create($moduleHelper->getLogDirectoryPath());

        if (!$directory->isExist($filename)) {
            return false;
        }

        return true;
    }

    private function getLatestUpgradeLogFileName()
    {
        /** @var \Ess\M2eProUpdater\Helper\M2ePro $m2eProHelper */
        $m2eProHelper = $this->helperFactory->getObject('M2ePro');
        $latestVersion  = $m2eProHelper->getLatestAvailableVersion();

        $fileName = AbstractModel::LOG_FILE_NAME_MASK;
        $latestVersion && $fileName = str_replace('%ver%', $latestVersion, $fileName);

        return $fileName;
    }

    //########################################
}