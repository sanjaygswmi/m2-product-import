<?php


namespace Ttm\HelloPrint\Model\Import;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Psr\Log\LoggerInterface;

use Ttm\HelloPrint\Model\Import\ProductImport\RowValidatorInterface as ValidatorInterface;
use Ttm\HelloPrint\Model\HelloPrint;

class ProductImport extends AbstractEntity
{
    const VARIANTKEY = 'variantkey';
    const SKU = 'sku';


    /** @inheritdoc */
    protected $masterAttributeCode = VARIANTKEY;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates
        = [
            ValidatorInterface::ERROR_SKU_IS_EMPTY => 'Sku is empty',
            ValidatorInterface::ERROR_VARIANTKEY_IS_EMPTY => 'Variant Key is empty',
            ValidatorInterface::ERROR_INVALID_ROW => 'Either SKU or Variant Key is empty',
            // ValidatorInterface::ERROR_INVALID_IS_ACTIVE => 'Status in\'t exist',
            // ValidatorInterface::ERROR_INVALID_UNIT_PRICE => 'Unit Price in\'t exist',
            // ValidatorInterface::ERROR_INVALID_WEBSITE_ID => 'Website in\'t exist',
            // ValidatorInterface::ERROR_INVALID_UNIT_NAME => 'Unit name not exist',
        ];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::SKU];

    /** @inheritdoc */
    protected $_availableBehaviors
        = [
            Import::BEHAVIOR_APPEND,
            Import::BEHAVIOR_REPLACE,
            Import::BEHAVIOR_DELETE
        ];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = false;

    /**
     * Valid column names
     *
     * @var array
     */
    protected $validColumnNames
        = [
            self::SKU,
            self::VARIANTKEY
        ];

    /**
     * @var Auth
     */
    protected $_auth;

   
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        // BulkDiscount $bulkDiscountModel,
        // CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        array $data = []
    ) {
        // $this->bulkDiscountModel = $bulkDiscountModel;
        // $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $data["entity"] = "catalog_product";
       
        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);
    }

    public function getEntityTypeCode()
    {
        // return 'product_import';
        return 'catalog_product';
        // return false;
    }

    public function getValidColumnNames()
     {
          return $this->validColumnNames;
     }
    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $logger->log(100, print_r( "validateRow is called", 100 ) );

        $code = false;

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        // BEHAVIOR_DELETE use specific validation logic
        if (Import::BEHAVIOR_DELETE === $this->getBehavior()) {
            if (!isset($rowData[self::VARIANTKEY])) {
                $this->addRowError(ValidatorInterface::ERROR_VARIANTKEY_IS_EMPTY, $rowNum);

                return false;
            }

            return true;
        }

        if (isset($rowData[self::SKU])) {
            $code = $rowData[self::SKU];
        }

        if ($code === false) {
            $this->addRowError(ValidatorInterface::ERROR_SKU_IS_EMPTY, $rowNum);
        }

    
        if ( !$rowData[self::VARIANTKEY] ) {
            $this->addRowError(ValidatorInterface::VARIANTKEY, $rowNum);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

   
    protected function _importData()
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->replaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveEntity();
                break;
            default:
                break;
        }

        return true;
    }

    
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();

        return $this;
    }

   
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();

        return $this;
    }

    
    public function deleteEntity()
    {
        $listCode = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowCode = $rowData[self::VARIANTKEY];
                    $listCode[] = $rowCode;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        if ($listCode) {
            $this->deleteEntityFinish(array_unique($listCode));
        }

        return $this;
    }

    protected function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $listCode = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
        
            $entityList = [];
        
            foreach ($bunch as $rowNum => $rowData) {
        
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_INVALID_ROW, $rowNum);
                    continue;
                }
        
                if ( $this->getErrorAggregator()->hasToBeTerminated() ) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowCode                = $rowData[self::SKU];
                $listCode[]             = $rowCode;
                $entityList[$rowCode][] = $rowData;
            }
        
            if ( $behavior === Import::BEHAVIOR_REPLACE ) {
        
                if ( $listCode && $this->deleteEntityFinish( array_unique( $listCode ) ) ) {
                    $this->saveEntityFinish($entityList);
                }
        
            } elseif ($behavior === Import::BEHAVIOR_APPEND) {
                $this->saveEntityFinish($entityList);
            }
        }

        return $this;
    }

    /**
     * @param array $entityData
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function saveEntityFinish(array $entityData)
    {
        if ($entityData) {
            $entityIn = [];

            echo "<pre>";
            print_r($entityData);
            echo "</pre>";
            die;

            foreach ($entityData as $entityRows) {
                foreach ($entityRows as $row) {


                    $row        = array_map('trim', $row);
                    $entityIn[] = $row;
                    $rowData    = $row;
                    $keyarray   = array_keys($row);
                    $keyarray   = array_map('trim', $keyarray);                    

                    for( $i = 2; $i < COUNT($row); $i++ ){

                        $unit               = substr($keyarray[$i], 6);
                        $price              = $row[$keyarray[$i]];
                        $data['website_id'] = trim( $row['website_id'] );
                        $data['sku']        = trim( $row['sku'] );
                        $data['is_active']  = '1';
                        $data['unit_name']  = trim( $unit );
                        $data['unit_price'] = trim( $row[$keyarray[$i]] );

                        //remove duplicate entery
                        // $this->checkDuplicateEntery($data);
                        //end
                        
                        /*$this->_connection->insertOnDuplicate($this->getBulDiscountTable(), $data, [
                            'website_id',
                            'sku',
                            'is_active',
                            'unit_name',
                            'unit_price',
                        ]);*/
                    }
                }
            }
        }

        return $this;
    }

    /*public function checkDuplicateEntery($row){
        try{
            $disucountCollection = $this->collectionFactory->create()->addFieldToFilter('sku', ['eq' => $row['sku']])->addFieldToFilter('website_id', ['eq' => $row['website_id']])->addFieldToFilter('unit_name', ['eq' => $row['unit_name']]);
            foreach ($disucountCollection->getItems() as $item) {
                $item->delete();
            }
        }catch(\Exception $e){
            \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->log(100,print_r($e->getMessage(),true));
        }
    }*/

    /**
     * @param array $listCode
     *
     * @return bool
     */
    protected function deleteEntityFinish(array $listCode)
    {
        if ($listCode) {
            try {
                /*$this->countItemsDeleted += $this->_connection->delete(
                    $this->getBulDiscountTable(),
                    $this->_connection->quoteInto('sku IN (?)', $listCode)
                );*/

                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

   
    /*public function getBulDiscountTable()
    {
        return $this->bulkDiscountModel->getResource()->getMainTable();
    }*/

}
