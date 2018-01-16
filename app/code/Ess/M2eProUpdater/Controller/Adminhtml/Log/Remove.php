<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Controller\Adminhtml\Log;

use Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as ConfigStructure;

class Remove extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    protected $directoryWriterFactory;

    /** @var \Ess\M2eProUpdater\Helper\Factory */
    protected $helperFactory;

    //########################################

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory
    ) {
        parent::__construct($context);
        $this->directoryWriterFactory = $directoryWriterFactory;
        $this->helperFactory          = $helperFactory;
    }

    //########################################

    public function execute()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');

        $fileName = $this->getRequest()->getParam('file_name');
        $directory = $this->directoryWriterFactory->create($moduleHelper->getLogDirectoryPath());

        if (!$directory->isFile($fileName)) {
            $this->getMessageManager()->addErrorMessage("Log is not exists [{$fileName}]");
        } else {
            $this->getMessageManager()->addSuccessMessage('Log has been removed.');
        }

        $directory->delete($fileName);

        return $this->_redirect('adminhtml/system_config/edit', [
            'section' => ConfigStructure::INSTALLATION_UPGRADE_SECTION
        ]);
    }

    //########################################
}