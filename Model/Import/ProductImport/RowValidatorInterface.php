<?php

namespace Ttm\HelloPrint\Model\Import\ProductImport;

use Magento\Framework\Validator\ValidatorInterface;
use Ttm\HelloPrint\Model\Import\ProductImport;


interface RowValidatorInterface extends ValidatorInterface
{
    const ERROR_SKU_IS_EMPTY = 'skuIsEmpty';
    const ERROR_INVALID_IS_ACTIVE = 'invalidStatus';
    const ERROR_INVALID_UNIT_PRICE = 'invalidUnitPrice';
    const ERROR_INVALID_WEBSITE_ID = 'invalidWebsite';
    const ERROR_INVALID_UNIT_NAME = 'invalidBalance';
    const VALUE_ALL = 'all'; #Value that means all entities (e.g. websites, groups etc.)
   
    public function init($context);
}
