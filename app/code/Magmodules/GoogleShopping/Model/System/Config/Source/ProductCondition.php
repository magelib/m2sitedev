<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ProductCondition implements ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=>'new', 'label'=> __('New')],
            ['value'=>'refurbished', 'label'=> __('Refurbished')],
            ['value'=>'used', 'label'=> __('Uses')],
        ];
    }
}
