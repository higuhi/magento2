<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Framework\Store\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Customer\Model\GroupManagement
 */
class GroupManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    protected $groupManagement;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->groupManagement = $this->objectManager->get('Magento\Customer\Api\GroupManagementInterface');
    }

    /**
     * @param $testGroup
     * @param $storeId
     *
     * @dataProvider getDefaultGroupDataProvider
     */
    public function testGetDefaultGroupWithStoreId($testGroup, $storeId)
    {
        $this->assertDefaultGroupMatches($testGroup, $storeId);
    }

    /**
     * @magentoDataFixture Magento/Core/_files/second_third_store.php
     */
    public function testGetDefaultGroupWithNonDefaultStoreId()
    {        /** @var \Magento\Framework\Store\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\Store\StoreManagerInterface');
        $nonDefaultStore = $storeManager->getStore('secondstore');
        $nonDefaultStoreId = $nonDefaultStore->getId();
        /** @var \Magento\Framework\App\MutableScopeConfig $scopeConfig */
        $scopeConfig = $this->objectManager->get('Magento\Framework\App\MutableScopeConfig');
        $scopeConfig->setValue(
            \Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID,
            2,
            \Magento\Framework\Store\ScopeInterface::SCOPE_STORE,
            'secondstore'
        );
        $testGroup = ['id' => 2, 'code' => 'Wholesale', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->assertDefaultGroupMatches($testGroup, $nonDefaultStoreId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetDefaultGroupWithInvalidStoreId()
    {
        $storeId = 1234567;
        $this->groupManagement->getDefaultGroup($storeId);
    }

    public function testIsReadonlyWithGroupId()
    {
        $testGroup = ['id' => 3, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->assertEquals(false, $this->groupManagement->isReadonly($testGroup['id']));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testIsReadonlyWithInvalidGroupId()
    {
        $testGroup = ['id' => 4, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'];
        $this->groupManagement->isReadonly($testGroup['id']);
    }

    public function testGetNotLoggedInGroup()
    {
        $notLoggedInGroup = $this->groupManagement->getNotLoggedInGroup();
        $this->assertEquals(GroupManagement::NOT_LOGGED_IN_ID, $notLoggedInGroup->getId());
    }

    public function testGetLoggedInGroups()
    {
        $loggedInGroups = $this->groupManagement->getLoggedInGroups();
        foreach ($loggedInGroups as $group) {
            $this->assertNotEquals(GroupManagement::NOT_LOGGED_IN_ID, $group->getId());
            $this->assertNotEquals(GroupManagement::CUST_GROUP_ALL, $group->getId());
        }
    }

    public function testGetAllGroup()
    {
        $allGroup = $this->groupManagement->getAllCustomersGroup();
        $this->assertEquals(32000, $allGroup->getId());
    }

    /**
     * @return array
     */
    public function getDefaultGroupDataProvider()
    {
        /** @var \Magento\Framework\Store\StoreManagerInterface  $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get('Magento\Framework\Store\StoreManagerInterface');
        $defaultStoreId = $storeManager->getStore()->getId();
        return [
            'no store id' => [
                ['id' => 1, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'],
                null,
            ],
            'default store id' => [
                ['id' => 1, 'code' => 'General', 'tax_class_id' => 3, 'tax_class_name' => 'Retail Customer'],
                $defaultStoreId,
            ],
        ];
    }

    /**
     * @param $testGroup
     * @param $storeId
     */
    private function assertDefaultGroupMatches($testGroup, $storeId)
    {
        $group = $this->groupManagement->getDefaultGroup($storeId);
        $this->assertEquals($testGroup['id'], $group->getId());
        $this->assertEquals($testGroup['code'], $group->getCode());
        $this->assertEquals($testGroup['tax_class_id'], $group->getTaxClassId());
        $this->assertEquals($testGroup['tax_class_name'], $group->getTaxClassName());
    }
}
