<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendRecommendations\Test\Integration\ViewModel\Html\Head;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\Frontend\Constants as FrontendConstants;
use Klevu\Frontend\Exception\InvalidIsEnabledDeterminerException;
use Klevu\Frontend\Service\Provider\CustomerGroupPricingEnabledProvider;
use Klevu\Frontend\ViewModel\Html\Head\CustomerGroupSuffix;
use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendApi\ViewModel\Html\Head\AddToCartInterface;
use Klevu\FrontendRecommendations\ViewModel\Html\Head\CustomerGroupSuffix as CustomerGroupSuffixVirtualType;
use Klevu\TestFixtures\Customer\CustomerGroupTrait;
use Klevu\TestFixtures\Customer\Group\CustomerGroupFixturePool;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\State as AppState;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers CustomerGroupSuffix
 * @method CustomerGroupSuffix instantiateTestObject(?array $arguments = null)
 * @method CustomerGroupSuffix instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class CustomerGroupSuffixViewModelTest extends TestCase
{
    use CustomerGroupTrait;
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = CustomerGroupSuffixVirtualType::class; // @phpstan-ignore-line
        $this->interfaceFqcn = ArgumentInterface::class;
        $this->implementationForVirtualType = CustomerGroupSuffix::class;
        $this->objectManager = Bootstrap::getObjectManager();

        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
        $this->customerGroupFixturePool = $this->objectManager->get(CustomerGroupFixturePool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->customerGroupFixturePool->rollback();
        $this->storeFixturesPool->rollback();
    }

    public function testIsEnabled_ReturnsFalse_WhenStoreNotIntegrated(): void
    {
        $viewModel = $this->instantiateTestObject();
        $this->assertFalse(condition: $viewModel->isEnabled());
    }

    public function testIsEnabled_ReturnsFalse_WhenStoreIntegrated_CustomGroupPriceDisabledOnFrontend(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-123456789',
            restAuthKey: '1234567890ABCDEFGHI',
        );
        ConfigFixture::setForStore(
            path: CustomerGroupPricingEnabledProvider::XML_PATH_USE_CUSTOMER_GROUP_PRICING,
            value: 0,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertFalse(condition: $viewModel->isEnabled());
    }

    public function testIsEnabled_ReturnsFalse_WhenStoreIntegrated_AllModulesDisabled_CustomGroupPriceEnabledOnFrontend(): void // phpcs:ignore Generic.Files.LineLength.TooLong
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-123456789',
            restAuthKey: '1234567890ABCDEFGHI',
        );
        ConfigFixture::setForStore(
            path: CustomerGroupPricingEnabledProvider::XML_PATH_USE_CUSTOMER_GROUP_PRICING,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertFalse(condition: $viewModel->isEnabled());
    }

    public function testIsEnabled_ReturnTrue_WhenStoreIntegrated_CatNavEnabled_CustomGroupPriceEnabledOnFrontend(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());

        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-123456789',
            restAuthKey: '1234567890ABCDEFGHI',
        );
        ConfigFixture::setForStore(
            path: 'klevu_frontend/recommendations/enabled',
            value: 1,
            storeCode: $storeFixture->getCode(),
        );
        ConfigFixture::setForStore(
            path: CustomerGroupPricingEnabledProvider::XML_PATH_USE_CUSTOMER_GROUP_PRICING,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );

        $viewModel = $this->instantiateTestObject();
        $this->assertTrue(condition: $viewModel->isEnabled());
    }

    /**
     * @dataProvider dataProvider_invalidIsEnabledConditionType
     */
    public function testIsEnabled_LogsException_InProductionMode_WhenConfigInvalid(mixed $invalidType): void
    {
        $errorMessage = sprintf(
            'IsEnabledCondition "%s" must be instance of %s; %s received',
            'klevu_integrated',
            IsEnabledConditionInterface::class,
            get_debug_type($invalidType),
        );

        $mockLogger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                'Method: {method}, Error: {error}',
                [
                    'method' => 'Klevu\Frontend\ViewModel\Html\Head\CustomerGroupSuffix::isEnabled',
                    'error' => $errorMessage,
                ],
            );

        $mockAppState = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAppState->expects($this->once())
            ->method('getMode')
            ->willReturn(AppState::MODE_PRODUCTION);

        /** @var AddToCartInterface $viewModel */
        $viewModel = $this->instantiateTestObject([
            'appState' => $mockAppState,
            'logger' => $mockLogger,
            'isEnabledConditions' => [
                'klevu_integrated' => $invalidType,
            ],
        ]);
        $this->assertFalse($viewModel->isEnabled());
    }

    /**
     * @dataProvider dataProvider_invalidIsEnabledConditionType
     */
    public function testIsEnabled_ThrowsException_InDeveloperMode_WhenConfigInvalid(mixed $invalidType): void
    {
        $errorMessage = sprintf(
            'IsEnabledCondition "%s" must be instance of %s;',
            'klevu_integrated',
            IsEnabledConditionInterface::class,
        );

        $this->expectException(InvalidIsEnabledDeterminerException::class);
        $this->expectExceptionMessage($errorMessage);

        $mockLogger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $mockLogger->expects($this->never())
            ->method('error');

        $mockAppState = $this->getMockBuilder(AppState::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAppState->expects($this->once())
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);

        /** @var AddToCartInterface $viewModel */
        $viewModel = $this->instantiateTestObject([
            'appState' => $mockAppState,
            'logger' => $mockLogger,
            'isEnabledConditions' => [
                'klevu_integrated' => $invalidType,
            ],
        ]);
        $viewModel->isEnabled();
    }

    /**
     * @return mixed[][]
     */
    public function dataProvider_invalidIsEnabledConditionType(): array
    {
        return [
            [null],
            [false],
            [true],
            [1],
            [1.23],
            ['string'],
            [new DataObject()],
        ];
    }

    public function testGetCustomerDataLoadedEventName_ReturnsExpectedValue(): void
    {
        $viewModel = $this->instantiateTestObject();
        $this->assertSame(
            expected: FrontendConstants::JS_EVENTNAME_CUSTOMER_DATA_LOADED,
            actual: $viewModel->getCustomerDataLoadedEventName(),
        );
    }

    public function testGetCustomerDataLoadErrorEventName_ReturnsExpectedValue(): void
    {
        $viewModel = $this->instantiateTestObject();
        $this->assertSame(
            expected: FrontendConstants::JS_EVENTNAME_CUSTOMER_DATA_LOAD_ERROR,
            actual: $viewModel->getCustomerDataLoadErrorEventName(),
        );
    }
}
