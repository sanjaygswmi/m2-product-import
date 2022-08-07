<?php
namespace Ttm\HelloPrint\Setup;

use \Magento\Eav\Setup\EavSetupFactory;
use \Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{    
    protected $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();    
        $eavSetup = $this->eavSetupFactory->create();    
        $entityTypeId = 149; // value for eav_entity_type table, here catalog_product value is 4
        $eavSetup->removeAttribute($entityTypeId, 'erp_brand_id');
        $setup->endSetup();    

        $setup->startSetup();    
        $eavSetup = $this->eavSetupFactory->create();    
        $entityTypeId = 150; // value for eav_entity_type table, here catalog_product value is 4
        $eavSetup->removeAttribute($entityTypeId, 'erp_taxable');    
        $setup->endSetup();    

        $setup->startSetup();    
        $eavSetup = $this->eavSetupFactory->create();    
        $entityTypeId = 151; 
        $eavSetup->removeAttribute($entityTypeId, 'erp_tax');    
        $setup->endSetup();    

        $setup->startSetup();    
        $eavSetup = $this->eavSetupFactory->create();    
        $entityTypeId = 152;
        $eavSetup->removeAttribute($entityTypeId, 'erp_city');    
        $setup->endSetup();            
    }

}

