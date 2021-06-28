<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Add product attribute to attribute set fixture
 */
class AddProductAttributeToAttributeSet implements RevertibleDataFixtureInterface
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param ServiceFactory $serviceFactory
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        EavSetup $eavSetup
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->eavSetup = $eavSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
        $data = array_merge(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'attribute_code' => 'fixture_attribute_1',
                'sort_order' => 0,
            ],
            $data
        );

        $service = $this->serviceFactory->create(ProductAttributeManagementInterface::class, 'assign');
        $service->execute($data);

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function revert(array $data = []): void
    {
        $service = $this->serviceFactory->create(ProductAttributeManagementInterface::class, 'unassign');
        $service->execute(
            [
                'attribute_set_id' => $data['attribute_set_id'],
                'attribute_code' => $data['attribute_code'],
            ]
        );
    }
}
