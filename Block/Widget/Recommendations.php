<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendRecommendations\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Recommendations extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "widget/recommendations.phtml"; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore, SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint, Generic.Files.LineLength.TooLong
}
