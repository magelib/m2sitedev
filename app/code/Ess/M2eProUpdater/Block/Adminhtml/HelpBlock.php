<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml;

use Magento\Backend\Block\Widget;

/**
 * @method void setTooltiped()
 * @method void setNoHide()
 * @method void setNoCollapse()
 */
class HelpBlock extends Widget
{
    protected $_template = 'Ess_M2eProUpdater::help_block.phtml';

    //########################################

    public function getId()
    {
        if (null === $this->getData('id') && $this->getContent()) {
            $this->setData('id', 'block_notice_' . crc32($this->getContent()));
        }
        return $this->getData('id');
    }

    protected function _toHtml()
    {
        if ($this->getContent()) {
            return parent::_toHtml();
        }

        return '';
    }

    //########################################
}