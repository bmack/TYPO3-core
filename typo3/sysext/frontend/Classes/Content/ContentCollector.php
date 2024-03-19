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

namespace TYPO3\CMS\Frontend\Content;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentCollector
{
    public function collect(
        ContentObjectRenderer $contentObjectRenderer,
        string $table,
        array $select,
        ContentSlideMode $slideMode = ContentSlideMode::None
    ): array
    {
        $slideCollectReverse = false;
        $collect = false;
        switch ($slideMode) {
            case ContentSlideMode::Slide:
                $slide = true;
                break;
            case ContentSlideMode::Collect:
                $slide = true;
                $collect = true;
                break;
            case ContentSlideMode::CollectReverse:
                $slide = true;
                $collect = true;
                $slideCollectReverse = true;
                break;
            default:
                $slide = false;
        }
        $again = false;
        $totalRecords = [];

        do {
            $recordsOnPid = $contentObjectRenderer->getRecords($table, $select);

            if ($slideCollectReverse) {
                $totalRecords = array_merge($totalRecords, $recordsOnPid);
            } else {
                $totalRecords = array_merge($recordsOnPid, $totalRecords);
            }
            if ($slide) {
                $select['pidInList'] = $contentObjectRenderer->getSlidePids($select['pidInList'] ?? '', $select['pidInList.'] ?? [],);
                if (isset($select['pidInList.'])) {
                    unset($select['pidInList.']);
                }
                $again = $select['pidInList'] !== '';
            }
        } while ($again && $slide && ($totalRecords === [] && $collect));
        return $totalRecords;
    }
}
