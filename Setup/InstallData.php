<?php
namespace Ttm\HelloPrint\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;

class InstallData implements InstallDataInterface
{

	private $eavSetupFactory;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		\Magento\Eav\Model\Config $eavConfig,
		\Magento\Customer\Model\ResourceModel\Attribute $attributeResource
	){
		$this->eavSetupFactory = $eavSetupFactory;
		$this->eavConfig = $eavConfig;
		$this->attributeResource = $attributeResource;
	}

	public function install(
		ModuleDataSetupInterface $setup,
		ModuleContextInterface $context
	)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

		//create product attribute
		$eavSetup->addAttribute(
			Product::ENTITY,
			'hp_variant_key',
			[
				'type' => 'varchar',
				'backend' => '',
				'frontend' => '',
				'label' => 'HelloPrint Variant Key',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => ''
			]
		);

		$eavSetup->addAttribute(
			Product::ENTITY,
			'hp_sku',
			[
				'type' => 'text',
				'backend' => '',
				'frontend' => '',
				'label' => 'HelloPrint SKU',
				'input' => 'text',
				'class' => '',
				'source' => '',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'user_defined' => false,
				'default' => '',
				'searchable' => false,
				'filterable' => false,
				'comparable' => false,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'apply_to' => ''
			]
		);

		$eavSetup->removeAttribute( Product::ENTITY, 'erp_brand_id');
		$eavSetup->removeAttribute( Product::ENTITY, 'erp_taxable');
		$eavSetup->removeAttribute( Product::ENTITY, 'erp_tax');
		$eavSetup->removeAttribute( Product::ENTITY, 'erp_city');

	}
}