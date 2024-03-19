<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Frontend\DataProcessing;


use TYPO3\CMS\Frontend\Content\ContentCollector;
use TYPO3\CMS\Frontend\Content\ContentSlideMode;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ContentFetchingProcessor implements DataProcessorInterface
{
    public function __construct(protected readonly ContentCollector $contentCollector) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        // Set the target variable
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'content');

        // Find automated information from TCA
        $items = $this->contentCollector->collect(
            $cObj,
            $processorConfiguration['table'],
            $processorConfiguration['select.'] ?? [],
            ContentSlideMode::tryFrom($processorConfiguration['slideMode'] ?? null)
        );

        $processedData[$targetVariableName] = $items;
        return $processedData;
    }
}
