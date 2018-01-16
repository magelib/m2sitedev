<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magmodules\GoogleShopping\Helper\Source as SourceHelper;
use Magento\Framework\App\Request\Http;

class ParentAttributes implements ArrayInterface
{

    protected $source;
    protected $request;

    public function __construct(
        Http $request,
        SourceHelper $source
    ) {
        $this->source = $source;
        $this->request = $request;
    }
        
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $attributes = [];
        $storeId = $this->request->getParam('store');
        $source = $this->source->getAttributes($storeId, 'parent');
        $exclude = ['g:id','g:link','g:is_bundle','g:item_group_id','g:price'];
        foreach ($source as $key => $attribute) {
            if (!in_array($attribute['label'], $exclude)) {
                $label = str_replace('_', ' ', $attribute['label']);
                $label = str_replace(['g:','G:'], '', $label);
                $attributes[] = [
                    'value' => $key,
                    'label' => ucwords($label),
                ];
            }
        }
        return $attributes;
    }
}
