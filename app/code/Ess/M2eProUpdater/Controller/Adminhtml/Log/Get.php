<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Controller\Adminhtml\Log;

use Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as ConfigStructure;

class Get extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory */
    protected $directoryReaderFactory;

    /** @var \Ess\M2eProUpdater\Helper\Factory */
    protected $helperFactory;

    //########################################

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\Directory\ReadFactory $directoryReaderFactory,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory
    ) {
        parent::__construct($context);
        $this->directoryReaderFactory = $directoryReaderFactory;
        $this->helperFactory          = $helperFactory;
    }

    //########################################

    public function execute()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');

        $fileName = $this->getRequest()->getParam('file_name');
        $directory = $this->directoryReaderFactory->create($moduleHelper->getLogDirectoryPath());

        if (!$directory->isExist($fileName) || !$directory->isFile($fileName)) {

            $this->getMessageManager()->addErrorMessage("Log is not exists [{$fileName}]");
            return $this->_redirect('adminhtml/system_config/edit', [
                'section' => ConfigStructure::INSTALLATION_UPGRADE_SECTION
            ]);
        }

        $absolutePath = $directory->getAbsolutePath($fileName);

        $this->getResponse()->setHeader('Content-type', 'text/plain; charset=UTF-8');
        $this->getResponse()->setHeader('Content-length', filesize($absolutePath));
        $this->getResponse()->setHeader('Content-Disposition', 'attachment' . '; filename=' .basename($absolutePath));

        $this->getResponse()->sendHeaders();

        readfile($absolutePath);
        die;
    }

    //########################################
}