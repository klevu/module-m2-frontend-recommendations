<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendRecommendations\Service\IsEnabledCondition;

use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendApi\Service\Provider\SettingsProviderInterface;

class IsRecommendationsEnabledCondition implements IsEnabledConditionInterface
{
    /**
     * @var SettingsProviderInterface
     */
    private readonly SettingsProviderInterface $recommendationsEnabledProvider;

    /**
     * @param SettingsProviderInterface $recommendationsEnabledProvider
     */
    public function __construct(SettingsProviderInterface $recommendationsEnabledProvider)
    {
        $this->recommendationsEnabledProvider = $recommendationsEnabledProvider;
    }

    /**
     * @return bool
     * @throws OutputDisabledException
     */
    public function execute(): bool
    {
        return (bool)$this->recommendationsEnabledProvider->get();
    }
}
