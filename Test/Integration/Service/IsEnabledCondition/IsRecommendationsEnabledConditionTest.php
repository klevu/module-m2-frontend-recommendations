<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendRecommendations\Test\Integration\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendRecommendations\Service\IsEnabledCondition\IsRecommendationsEnabledCondition;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendRecommendations\Service\IsEnabledCondition\IsRecommendationsEnabledCondition
 * @magentoAppArea frontend
 */
class IsRecommendationsEnabledConditionTest extends TestCase
{
    use ObjectInstantiationTrait;
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

        $this->implementationFqcn = IsRecommendationsEnabledCondition::class; // @phpstan-ignore-line
        $this->interfaceFqcn = IsEnabledConditionInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/recommendations/enabled 1
     * @magentoConfigFixture default_store klevu_frontend/recommendations/enabled 0
     */
    public function testExecute_ReturnsFalse_WhenDisabled(): void
    {
        /** @var IsRecommendationsEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertFalse(condition: $service->execute());
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/recommendations/enabled 0
     * @magentoConfigFixture default_store klevu_frontend/recommendations/enabled 1
     */
    public function testExecute_ReturnsTrue_WhenEnabled(): void
    {
        /** @var IsRecommendationsEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }
}
