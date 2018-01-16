<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper\Magento;

use Ess\M2eProUpdater\Helper\Factory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Config extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    /** @var \Magento\Framework\App\ResourceConnection ResourceConnection */
    private $resourceConnection;

    //########################################

    public function __construct(
        Factory $helperFactory,
        Context $context,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($helperFactory, $context);
        $this->resourceConnection = $resourceConnection;
    }

    //########################################

    public function getValue($path, $default = false)
    {
        $select = $this->resourceConnection->getConnection()
            ->select()
            ->from($this->resourceConnection->getTableName('core_config_data'), 'value')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', $path);

        $result = $this->resourceConnection->getConnection()->fetchOne($select);
        return $result === false ? $default : $result;
    }

    public function setValue($path, $value)
    {
        if ($this->getValue($path) === false) {
            $this->resourceConnection->getConnection()->insert(
                $this->resourceConnection->getTableName('core_config_data'),
                [
                    'scope'    => 'default',
                    'scope_id' => 0,
                    'path'     => $path,
                    'value'    => $value
                ]
            );
            return;
        }

        $this->resourceConnection->getConnection()->update(
            $this->resourceConnection->getTableName('core_config_data'),
            ['value' => $value],
            [
                'scope = ?'    => 'default',
                'scope_id = ?' => 0,
                'path = ?'     => $path,
            ]
        );
    }

    //########################################
}