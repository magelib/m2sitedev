<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/*
 *  If you moved this file in another place, then please set a correct path to the bootstrap file
 */
require __DIR__ . '/../../../../app/bootstrap.php';

if (php_sapi_name() !== 'cli'){
    echo "You can run this from the command line only.";
    exit(1);
}

try {

    $params = $_SERVER;
    $params[StoreManager::PARAM_RUN_CODE] = 'admin';
    $params[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);

    /** @var \Ess\M2eProUpdater\Model\Cron\Runner $cronRunner */
    $cronRunner = $bootstrap->getObjectManager()->get('Ess\M2eProUpdater\Model\Cron\Runner');
    $cronRunner->process();

} catch (\Throwable $t) {

    echo $t->__toString();
    exit(1);

} catch (\Exception $e) {

    echo $e->__toString();
    exit(1);
}