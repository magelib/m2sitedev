<?php

namespace Gene\ApplePay\Api;

/**
 * Interface AuthInterface
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface AuthInterface
{
    /**
     * Returns details required to be able to submit a payment with apple pay.
     * @return \Gene\ApplePay\Api\Data\AuthDataInterface
     */
    public function get();
}
