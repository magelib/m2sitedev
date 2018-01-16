<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Controller\Adminhtml\Configuration;

use Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as ConfigStructure;

class Save extends \Magento\Backend\App\Action
{
    /** @var \Ess\M2eProUpdater\Helper\Magento\Config */
    private $configHelper;

    //########################################

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ess\M2eProUpdater\Helper\Magento\Config $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
    }

    //########################################

    public function execute()
    {
        $configPath  = $this->getRequest()->getParam('path');
        $configValue = $this->getRequest()->getParam('value');

        if (is_null($configPath) || is_null($configValue)) {
            $this->getMessageManager()->addErrorMessage('Some required params are missing.');
        } else {

            $this->configHelper->setValue(base64_decode($configPath), base64_decode($configValue));
            $this->getMessageManager()->addSuccessMessage('Saved.');
        }

        return $this->_redirect('adminhtml/system_config/edit', [
            'section' => ConfigStructure::INSTALLATION_UPGRADE_SECTION
        ]);
    }

    //########################################
}