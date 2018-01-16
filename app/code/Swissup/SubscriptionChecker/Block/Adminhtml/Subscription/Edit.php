<?php

namespace Swissup\SubscriptionChecker\Block\Adminhtml\Subscription;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize subscription activation block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Swissup_SubscriptionChecker';
        $this->_controller = 'adminhtml_subscription';

        parent::_construct();

        $this->setData('form_action_url', $this->getUrl('*/*/activate'));

        if ($this->getAuthorization()->isAllowed('Swissup_SubscriptionChecker::activate')) {
            $this->buttonList->update('save', 'label', __('Activate'));
        } else {
            $this->buttonList->remove('save');
        }

        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Activate SwissUpLabs Subscription');
    }
}
