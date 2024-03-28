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

namespace TYPO3\CMS\Fluid\ViewHelpers;

use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * ViewHelper to render a block
 */
final class RenderBlockViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('data', Record::class, 'Block data', false, []);
    }

    /**
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $data = $arguments['data'];


        $view = $renderingContext->getViewHelperVariableContainer()->getView();
        if (!$view) {
            throw new Exception(
                'The f:render ViewHelper was used in a context where the ViewHelperVariableContainer does not contain ' .
                'a reference to the View. Normally this is taken care of by the TemplateView, so most likely this ' .
                'error is because you overrode AbstractTemplateView->initializeRenderingContext() and did not call ' .
                '$renderingContext->getViewHelperVariableContainer()->setView($this) or parent::initializeRenderingContext. ' .
                'This is an issue you must fix in your code as f:renderBlock is fully unable to render anything without a View.'
            );
        }
        $subView = GeneralUtility::makeInstance(StandaloneView::class);
        $r = clone $view->getRenderingContext();
        $subView->setRequest($renderingContext->getRequest());
        $subView->getRenderingContext()->setTemplatePaths($r->getTemplatePaths());
        $subView->setTemplate(str_replace('.', '/', $data->getFullType()));
        // @todo: consider using the same variables
        $subView->assign('record', $data);
        try {
            $content = $subView->render();
        } catch (InvalidTemplateResourceException) {
            // Render via TypoScript as fallback
            /** @var CObjectViewHelper $cObjectViewHelper */
            $cObjectViewHelper = $view->getViewHelperResolver()->createViewHelperInstance('f', 'cObject');
            $blockType = GeneralUtility::camelCaseToLowerCaseUnderscored($data->getFullType());
            if (str_starts_with($blockType, 'content')) {
                $blockType = 'tt_' . $blockType;
            }
            $cObjectViewHelper->setArguments(['typoscriptObjectPath' => $blockType, 'data' => $data->getRawProperties()]);
            $cObjectViewHelper->setRenderingContext($subView->getRenderingContext());
            $content = $cObjectViewHelper->render();
        }
        return $content;
    }
}
