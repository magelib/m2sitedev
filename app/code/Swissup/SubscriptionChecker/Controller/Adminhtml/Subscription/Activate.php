<?php

namespace Swissup\SubscriptionChecker\Controller\Adminhtml\Subscription;

class Activate extends \Swissup\SubscriptionChecker\Controller\Adminhtml\Subscription
{
    const ADMIN_RESOURCE = 'Swissup_SubscriptionChecker::activate';

    /**
     * @var \Swissup\Core\Helper\PopupMessageManager
     */
    protected $popupMessageManager;

    /**
     * @var \Swissup\SubscriptionChecker\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Swissup\Core\Helper\PopupMessageManager $popupMessageManager,
        \Swissup\SubscriptionChecker\Helper\Data $helper
    ) {
        parent::__construct($context, $coreRegistry);
        $this->popupMessageManager = $popupMessageManager;
        $this->helper = $helper;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return $resultRedirect->setPath('*/*/index');
        }

        $session = $this->_objectManager->get('Magento\Backend\Model\Session');
        $session->setFormData($data);

        $model = $this->_objectManager->create('Swissup\Core\Model\Module');
        $model->load(self::MODULE_CODE)
            ->setNewStores(array(0))
            ->setIdentityKey($data['identity_key']);

        $result = $model->validateLicense();
        if (is_array($result) && isset($result['error'])) {
            $error = call_user_func_array('__', $result['error']);
            if (isset($result['response'])) {
                $this->popupMessageManager->addError(
                    $error,
                    $result['response'],
                    'SwissUpLabs subscription validation response'
                );
            } else {
                $this->messageManager->addError($error);
            }
            return $resultRedirect->setPath('*/*/index');
        }

        $model->up();

        $groupedErrors = $model->getInstaller()->getMessageLogger()->getErrors();
        if (count($groupedErrors)) {
            foreach ($groupedErrors as $type => $errors) {
                foreach ($errors as $error) {
                    if (is_array($error)) {
                        $message = $error['message'];
                    } else {
                        $message = $error;
                    }
                    $this->messageManager->addError($message);
                }
            }
            return $resultRedirect->setPath('*/*/index');
        }

        $session->setFormData(false);
        $this->messageManager->addSuccess(__("Subscription has been activated"));
        return $resultRedirect->setPath('*/*/index');
    }
}
