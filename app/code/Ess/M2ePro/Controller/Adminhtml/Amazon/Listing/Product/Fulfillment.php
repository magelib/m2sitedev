<?php

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Main;

abstract class Fulfillment extends Main
{
    protected function getSwitchFulfillmentResultMessage($result)
    {
        $messageType = '';
        $messageText = '';

        if ($result == \Ess\M2ePro\Helper\Data::STATUS_ERROR) {
            $messageType = 'error';
            $messageText = $this->__('
                Fulfillment was not switched. Please check Listing Log for more details.');
        }

        if ($result == \Ess\M2ePro\Helper\Data::STATUS_WARNING) {
            $messageType = 'warning';
            $messageText = $this->__('
                Fulfillment switching is in progress now but there are some warnings. Please check Listing Log
                for more details.');
        }

        if ($result == \Ess\M2ePro\Helper\Data::STATUS_SUCCESS) {
            $messageType = 'success';
            $messageText = $this->__('Fulfillment switching is in progress now. Please wait.');
        }

        return array(
            'type' => $messageType,
            'text' => $messageText
        );
    }
}