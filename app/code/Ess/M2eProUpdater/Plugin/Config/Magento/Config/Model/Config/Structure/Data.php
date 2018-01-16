<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure;

class Data extends \Ess\M2eProUpdater\Plugin\AbstractPlugin
{
    const M2EPRO_UPDATER_TAB_NAME      = 'm2epro_updater';
    const INSTALLATION_UPGRADE_SECTION = 'installation_upgrade';

    //########################################

    public function aroundGet($interceptor, \Closure $callback)
    {
        $result = $callback();

        if (isset($result['tabs']['m2epro'])) {

            unset($result['tabs'][self::M2EPRO_UPDATER_TAB_NAME]);

            $result['sections'][self::INSTALLATION_UPGRADE_SECTION]['tab'] = 'm2epro';
            $result['sections'][self::INSTALLATION_UPGRADE_SECTION]['label'] = __('Upgrade');
        }

        return $result;
    }

    //########################################
}