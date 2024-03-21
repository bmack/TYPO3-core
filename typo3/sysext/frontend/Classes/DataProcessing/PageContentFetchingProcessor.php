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

/**
 * All-in-one data processor that loads all content from the current page layout into
 * the template with a given identifier for each colPos, also respecting slideMode or
 * collect options based on the page layouts content columns.
 */
class PageContentFetchingProcessor implements DataProcessorInterface
{
    public function __construct(protected readonly ContentCollector $contentCollector) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        $pageInformation = $cObj->getRequest()->getAttribute('frontend.page.information');
        $processedData['page'] = $pageInformation;
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'content');
        $pageLayout = $pageInformation->getPageLayout();
        foreach ($pageLayout->getContentAreas() as $contentAreaData) {
            // Find automated information from TCA
            $items = $this->contentCollector->collect(
                $cObj,
                'tt_content',
                [
                    'where' => 'colPos=' . (int)$contentAreaData['colPos'],
                    'orderBy' => 'sorting',
                ],
                ContentSlideMode::tryFrom($contentAreaData['slideMode'] ?? null)
            );
            $contentAreaData['blocks'] = $items;
            $contentAreaName = $contentAreaData['identifier'] ?? 'column' . $contentAreaData['colPos'];
            $processedData[$targetVariableName][$contentAreaName] = $contentAreaData;
        }
        return $processedData;
    }
}
