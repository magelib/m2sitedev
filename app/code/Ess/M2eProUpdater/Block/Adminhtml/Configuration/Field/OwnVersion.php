<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration\Field;

use \Ess\M2eProUpdater\Block\Adminhtml\Configuration\AbstractField;

class OwnVersion extends AbstractField
{
    //########################################

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $helper */
        $helper = $this->helperFactory->getObject('Module');
        $element->setValue($helper->getComposerVersion());

        return parent::_getElementHtml($element);
    }

    //########################################

    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '" style="display: none;">' . $html . '</tr>';
    }

    //########################################
}