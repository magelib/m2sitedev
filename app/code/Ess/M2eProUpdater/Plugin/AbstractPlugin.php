<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Plugin;

abstract class AbstractPlugin
{
    protected $helperFactory;
    protected $modelFactory;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory
    )
    {
        $this->helperFactory = $helperFactory;
        $this->modelFactory  = $modelFactory;
    }

    //########################################
}