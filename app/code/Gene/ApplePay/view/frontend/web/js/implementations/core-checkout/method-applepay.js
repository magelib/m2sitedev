define(
    ['uiComponent', 'Magento_Checkout/js/model/payment/renderer-list'],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'gene_applepay',
                component: 'Gene_ApplePay/js/implementations/core-checkout/method-renderer/applepay'
            }
        );

        return Component.extend({});
    }
);