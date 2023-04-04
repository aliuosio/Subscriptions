<?php

declare(strict_types=1);

namespace Osio\Subscriptions\Setup\Patch\Data;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Validator\ValidateException;
use Osio\Subscriptions\Helper\Data as Helper;
use Psr\Log\LoggerInterface;
use Zend_Validate_Exception;


class IsProductSubscribable implements DataPatchInterface
{
    public const NAME = 'subscribable';


    public function __construct(
        private readonly EavSetupFactory              $eavSetupFactory,
        private readonly ModuleDataSetupInterface     $moduleDataSetup,
        private readonly AttributeManagementInterface $attributeManagement,
        private readonly Config                       $config,
        private readonly LoggerInterface              $logger,
        private readonly Helper                       $helper
    )
    {
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply(): PatchInterface|IsProductSubscribable|static
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        try {
            $eavSetup->addAttribute(
                Product::ENTITY,
                IsProductSubscribable::NAME,
                [
                    'type' => 'int',
                    'group' => 'General',
                    'label' => $this->helper->getCode(),
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'sort_order' => 10,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'default' => '0',
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                ]
            );
        } catch (LocalizedException|Zend_Validate_Exception|ValidateException $e) {
            $this->logger->critical($e->getTraceAsString());
        }

        foreach ($eavSetup->getAllAttributeSetIds(Product::ENTITY) as $attributeSetId) {
            try {
                $this->attributeManagement->assign(
                    Product::ENTITY,
                    $attributeSetId,
                    $this->config->getAttributeGroupId((int)$attributeSetId, 'Product Details'),
                    IsProductSubscribable::NAME,
                    10
                );
            } catch (InputException|NoSuchEntityException $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
