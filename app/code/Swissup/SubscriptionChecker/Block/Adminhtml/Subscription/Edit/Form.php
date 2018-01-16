<?php

namespace Swissup\SubscriptionChecker\Block\Adminhtml\Subscription\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('subscription_form');
        $this->setTitle(__('Subscription Key'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Swissup\SubscriptionChecker\Model\Subscription $model */
        $model = $this->_coreRegistry->registry('subscription');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->getAuthorization()->isAllowed('Swissup_SubscriptionChecker::activate')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $form->setHtmlIdPrefix('subscription_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        $note = '';
        if ($model->getRemote()) {
            $link = $model->getRemote()->getIdentityKeyLink();
            $note = __(
                'Get your identity key at <a href="%1" title="%1" target="_blank">%1</a>',
                $link
            );
        }
        $fieldset->addField('identity_key', 'textarea', array(
            'name'  => 'identity_key',
            'required' => true,
            'disabled' => $isElementDisabled,
            'label' => __('Identity Key'),
            'title' => __('Identity Key'),
            'note'  => $note
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
