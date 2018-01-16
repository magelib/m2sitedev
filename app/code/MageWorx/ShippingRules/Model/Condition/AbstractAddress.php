<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Condition;

use Magento\SalesRule\Model\Rule\Condition\Address as SalesRuleAddress;

/**
 * Class AbstractAddress
 *
 * @method AbstractAddress setAttributeOption(array $array)
 * @method string getAttribute()
 * @method array getAttributeOption()
 */
class AbstractAddress extends SalesRuleAddress
{
    const WILDCARD_SYMBOL = '%';
    const ANY_CHAR_SYMBOL = '?';

    const POST_CODE_PARTS_LIMIT = 20;

    /**
     * Scalar operators used for the comparison purposes
     *
     * @var array
     */
    protected $scalarOperators = [
        '<=', '>', '>=', '<'
    ];

    /**
     * @param \MageWorx\ShippingRules\Helper\Data $helper
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $directoryCountry
     * @param \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion
     * @param \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods
     * @param \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods
     * @param array $data
     */
    public function __construct(
        \MageWorx\ShippingRules\Helper\Data $helper,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Directory\Model\Config\Source\Country $directoryCountry,
        \Magento\Directory\Model\Config\Source\Allregion $directoryAllregion,
        \Magento\Shipping\Model\Config\Source\Allmethods $shippingAllmethods,
        \Magento\Payment\Model\Config\Source\Allmethods $paymentAllmethods,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $directoryCountry,
            $directoryAllregion,
            $shippingAllmethods,
            $paymentAllmethods,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * Validate product attribute value for condition
     *
     * @param   object|array|int|string|float|bool $validatedValue product attribute value
     *
     * @return  bool
     */
    public function validateAttribute($validatedValue)
    {
        if ($this->getAttribute() == 'postcode' && $this->helper->isAdvancedPostCodeValidationEnabled()) {
            return $this->getIsValidPostCodeAdvanced($validatedValue);
        }

        return parent::validateAttribute($validatedValue);
    }

    /**
     * Get all available scalar operators
     *
     * @return array
     */
    public function getScalarOperators()
    {
        return $this->scalarOperators;
    }

    /**
     * Validate postcode attribute value for condition
     *
     * @param   object|array|int|string|float|bool $enteredValue product attribute value
     *
     * @return  bool
     */
    private function getIsValidPostCodeAdvanced($enteredValue)
    {
        if (is_object($enteredValue)) {
            return false;
        }

        $desiredPart = $this->getValueParsed();
        $operator = $this->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($this->isArrayOperatorType() xor is_array($desiredPart)) {
            return false;
        }

        // If operator is scalar and value is not scalar return false
        if ($this->isScalarOperator() && !$this->isScalarValue($enteredValue)) {
            return false;
        }

        // Result is false by default
        $result = false;

        switch ($operator) {
            case '==':
            case '!=':
                $result = $this->_compareValues($enteredValue, $desiredPart);
                break;

            case '<=':
            case '>':
                $result = $this->extendedPostcodeComparison($enteredValue, $desiredPart, '<=');
                break;

            case '>=':
            case '<':
                $result = $this->extendedPostcodeComparison($enteredValue, $desiredPart, '>=');
                break;

            case '{}':
            case '!{}':
                if ($this->isScalarValue($enteredValue) && is_array($desiredPart)) {
                    foreach ($desiredPart as $item) {
                        if (stripos($enteredValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($desiredPart)) {
                    if (is_array($enteredValue)) {
                        $result = array_intersect($desiredPart, $enteredValue);
                        $result = !empty($result);
                    } else {
                        return false;
                    }
                } else {
                    if (is_array($enteredValue)) {
                        $result = in_array($desiredPart, $enteredValue);
                    } else {
                        $result = $this->_compareValues($desiredPart, $enteredValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($enteredValue)) {
                    $result = count(array_intersect($enteredValue, (array)$desiredPart)) > 0;
                } else {
                    $desiredPart = (array)$desiredPart;
                    foreach ($desiredPart as $item) {
                        if ($this->_compareValues($enteredValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if (in_array($operator, ['!=', '>', '<', '!{}', '!()'])) {
            $result = !$result;
        }

        return $result;
    }

    /**
     * @param string $enteredValue
     * @param string $desiredPart
     * @param string $operator
     *
     * @return bool
     * @throws \Exception
     */
    private function extendedPostcodeComparison($enteredValue, $desiredPart, $operator)
    {
        $partsEnteredValue = $this->explodeStringByAlphaDigits($enteredValue);
        $partsDesired = $this->explodeStringByAlphaDigits($desiredPart);

        if (count($partsDesired) > static::POST_CODE_PARTS_LIMIT) {
            throw new \Exception(__('Something goes wrong during a post code parsing process.'));
        }

        $isValid = false;
        $i = -1;
        while ($i++ < count($partsDesired)) {
            if (!isset($partsEnteredValue[$i]) && isset($partsDesired[$i])) {
                // End of validation: entered value is invalid
                // because the desired part is more specific than entered value and can not be validated fully
                $isValid = false;
                break;
            }

            if (!isset($partsEnteredValue[$i]) && !isset($partsDesired[$i])) {
                // Normally end of validation
                break;
            }

            if (isset($partsEnteredValue[$i]) && !isset($partsDesired[$i])) {
                // Normally end of validation
                if ($isValid) {
                    $isValid = $this->helper->getPostcodeExcessiveValid();
                }
                break;
            }

            switch ($operator) {
                case '<=':
                    $isValid = $partsEnteredValue[$i] <= $partsDesired[$i];
                    break;
                case '>=':
                    $isValid = $partsEnteredValue[$i] >= $partsDesired[$i];
                    break;
                default:
                    $isValid = false;
            }

            if (!$isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * Case and type insensitive comparison of values
     *
     * @param string|int|float $validatedValue
     * @param string|int|float $desiredPart
     * @param bool $strict
     *
     * @return bool
     */
    protected function _compareValues($validatedValue, $desiredPart, $strict = true)
    {
        if ($this->getAttribute() == 'postcode' && $this->specialSymbolsFound($desiredPart)) {
            return $this->validatePostcode($validatedValue, $desiredPart, $strict);
        }

        return parent::_compareValues($validatedValue, $desiredPart, $strict);
    }

    /**
     * Validate postcode using wildcard
     *
     * @param $validatedValue
     * @param $desiredPart
     * @param bool|true $strict
     *
     * @return bool
     */
    protected function validatePostcode($validatedValue, $desiredPart, $strict = true)
    {
        $validatePattern = preg_quote($desiredPart, '~');
        $validatePattern = str_ireplace(static::WILDCARD_SYMBOL, '(.)+', $validatePattern);
        $validatePattern = str_ireplace(static::ANY_CHAR_SYMBOL, '(.){1,1}', $validatePattern);
        if ($strict) {
            $validatePattern = '^' . $validatePattern . '$';
        }
        $result = (bool)preg_match('~' . $validatePattern . '~iu', $validatedValue);

        return $result;
    }

    /**
     * Check is special symbols was found in the string
     *
     * @param $string
     *
     * @return bool
     */
    private function specialSymbolsFound($string)
    {
        return stripos($string, static::WILDCARD_SYMBOL) !== false ||
        stripos($string, static::ANY_CHAR_SYMBOL) !== false;
    }

    /**
     * Check currently used operator: is it scalar?
     *
     * @return bool
     */
    private function isScalarOperator()
    {
        $operator = $this->getOperatorForValidate();

        return in_array($operator, $this->getScalarOperators());
    }

    /**
     * Check: value is scalar or not?
     *
     * @param $value
     *
     * @return bool
     */
    private function isScalarValue($value)
    {
        return is_scalar($value);
    }

    /**
     * Explode string by digits and letters part
     *
     * @param $string
     *
     * @return array
     */
    private function explodeStringByAlphaDigits($string)
    {
        if (preg_match_all('~[a-zA-Z]+|\d+|[^\da-zA-Z]+~', $string, $chunks)) {
            return $chunks[0];
        }

        return [];
    }
}
