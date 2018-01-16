<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magmodules\GoogleShopping\Controller\Adminhtml\Actions;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magmodules\GoogleShopping\Model\Generate as GenerateModel;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;

class Preview extends Action
{

    protected $generate;
    protected $general;

    /**
     * Preview constructor.
     * @param Context $context
     * @param GeneralHelper $generalHelper
     * @param GenerateModel $generateModel
     */
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        GenerateModel $generateModel
    ) {
        $this->generate = $generateModel;
        $this->general = $generalHelper;
        parent::__construct($context);
    }

    /**
     * Execute function for preview of the Google Shopping feed in admin.
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if (!$this->general->getEnabled($storeId)) {
            $errorMsg = __('Please enable the extension before generating the feed.');
            $this->messageManager->addError($errorMsg);
            $this->_redirect('adminhtml/system_config/edit/section/magmodules_googleshopping');
        } else {
            if ($result = $this->generate->generateByStore($storeId, 'preview')) {
                $this->getResponse()->setHeader('Content-type', 'text/xml');
                $this->getResponse()->setBody(file_get_contents($result['path']));
            } else {
                $errorMsg = __('Unkown error.');
                $this->messageManager->addError($errorMsg);
                $this->_redirect('adminhtml/system_config/edit/section/magmodules_googleshopping');
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magmodules_GoogleShopping::config');
    }
}
