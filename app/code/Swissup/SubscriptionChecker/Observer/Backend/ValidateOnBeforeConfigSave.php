<?php

namespace Swissup\SubscriptionChecker\Observer\Backend;

class ValidateOnBeforeConfigSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Core\Helper\PopupMessageManager
     */
    protected $popupMessageManager;

    /**
     * @var \Swissup\SubscriptionChecker\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    public function __construct(
        \Swissup\Core\Helper\PopupMessageManager $popupMessageManager,
        \Swissup\SubscriptionChecker\Helper\Data $helper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Backend\Model\Session $session
    ) {
        $this->popupMessageManager = $popupMessageManager;
        $this->helper = $helper;
        $this->backendUrl = $backendUrl;
        $this->actionFlag = $actionFlag;
        $this->redirect = $redirect;
        $this->session = $session;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $section = $observer->getControllerAction()->getRequest()->getParam('section');
        if (!$this->helper->canValidateConfigSection($section)) {
            return;
        }

        $result = $this->helper->validateSubscription($section);
        if (is_array($result) && isset($result['error'])) {

            $this->session->setSwissupSkipValidation(true);
            $this->popupMessageManager->addWarning(__('Config has not been saved'));
            if (isset($result['response'])) {
                $this->popupMessageManager->addError(
                    $result['error'],
                    $result['response'],
                    'SwissUpLabs subscription validation response'
                );
            } else {
                $this->popupMessageManager->addError($result['error']);
            }

            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
            $observer->getControllerAction()->getResponse()->setRedirect(
                $this->backendUrl->getUrl(
                    '*/*/edit',
                    ['_current' => array('section', 'website', 'store')]
                )
            );
        }
    }
}
