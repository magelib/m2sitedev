<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml\Configuration;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Ess\M2eProUpdater\Block\Adminhtml\AbstractBlock;

class Help extends AbstractBlock implements RendererInterface
{
    //########################################

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('Ess\M2eProUpdater\Block\Adminhtml\HelpBlock', '', ['data' => [
            'content' => <<<HTML
<p>Here you can configure the functions of automatic Installation/Upgrade processes of M2E Pro Module.</p>
<p>
You can specify how you should be notified about new version available:
    <ul>
        <li>
            <strong>Do Not Notify</strong> - 
            no notification required;
        </li>
        <li>
            <strong>On each Extension Page (default)</strong> - 
            notifications block will be shown on each page of M2E Pro Module;
        </li>
        <li>
            <strong>On each Magento Page</strong> - 
            notifications block will be shown on each page of Magento;
        </li>
        <li>
            <strong>As Magento System Notification</strong> - 
            adds a notification using Magento global messages system.
        </li>
    </ul>
</p>
<p>
    In case the new version is available, you can plan the upgrade by pressing a Schedule Upgrade button. 
    Module will be upgraded during the next 10-15 minutes.
</p>
<br>
<p>
    <strong>Note:</strong> during the upgrade process of M2E Pro Extension, 
    the Magento will be in Maintenance Mode like during the upgrade via Magento Component Manager.
</p>
HTML
        ]]);

        $css = <<<HTML
<style>
    .scope-label { visibility: hidden }
    .entry-edit-head-link + a:before { content: '' !important; }
</style>
HTML;
        return $css . $helpBlock->toHtml();
    }

    //########################################
}