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

namespace TYPO3\CMS\Frontend\ContentObject;

use TYPO3\CMS\Core\Page\PageLayoutResolver;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * FLUIDPAGE Content Object.
 * Rendering a full page with Fluid, and does the following
 * - uses the templateName from the given Page Layout
 * - injects pageInformation, site and siteLanguage as variables by default
 * - merges all page settings (= TypoScript constants) into the settings variable
 *
 * In contrast to FLUIDTEMPLATE, by design this cObject
 * - does not handle Extbase specialities
 * - does not handle HeaderAssets and FooterAssets
 * - does not handle "template." and "file." resolving from cObject
 */
class FluidPageContentObject extends AbstractContentObject
{
    protected array $reservedVariables = ['data', 'current', 'site', 'siteLanguage', 'page'];

    public function __construct(
        protected readonly ContentDataProcessor $contentDataProcessor,
        protected readonly StandaloneView $view,
        protected readonly TypoScriptService $typoScriptService,
        protected readonly PageLayoutResolver $pageLayoutResolver,
    ) {}

    /**
     * Rendering the cObject, FLUIDTEMPLATE
     *
     * Configuration properties:
     * - layoutRootPaths array of filepath+stdWrap Root paths to layouts (fallback)
     * - partialRootPaths array of filepath+stdWrap Root paths to partials (fallback)
     * - variable array of cObjects, the keys are the variable names in fluid
     * - dataProcessing array of data processors which are classes to manipulate $data
     *
     * Example:
     * 10 = FLUIDTEMPLATE
     * 10.templateRootPaths.10 = EXT:site_configuration/Resources/Private/Templates/
     * 10.variables {
     *   mylabel = TEXT
     *   mylabel.value = Label from TypoScript coming
     * }
     *
     * @param array $conf Array of TypoScript properties
     * @return string The HTML output
     */
    public function render($conf = [])
    {
        $this->view->setRequest($this->request);

        if (!is_array($conf)) {
            $conf = [];
        }

        $this->setFormat($conf);
        $this->setTemplate($conf);
        $this->setLayoutRootPath($conf);
        $this->setPartialRootPath($conf);
        $this->assignSettings($conf);
        $variables = $this->getContentObjectVariables($conf);
        $variables = $this->contentDataProcessor->process($this->cObj, $conf, $variables);

        $this->view->assignMultiple($variables);

        $content = $this->renderFluidView();
        return $this->applyStandardWrapToRenderedContent($content, $conf);
    }

    protected function setTemplate(array $conf): void
    {
        if (!empty($conf['templateRootPaths.']) && is_array($conf['templateRootPaths.'])) {
            $templateRootPaths = $this->applyStandardWrapToFluidPaths($conf['templateRootPaths.']);
            $this->view->setTemplateRootPaths($templateRootPaths);
        }
        if (!empty($conf['templateName']) || !empty($conf['templateName.'])) {
            // Fetch the Fluid template by templateName
            $templateName = $this->cObj->stdWrapValue('templateName', $conf);
            $this->view->setTemplate($templateName);
        } else {
            // Fetch the Fluid template by the name of the Page Layout and underneath "Pages"
            $pageInformationObject = $this->request->getAttribute('frontend.page.information');
            $layout = $this->pageLayoutResolver->getLayoutForPage(
                $pageInformationObject->getPageRecord(),
                $pageInformationObject->getRootLine()
            );
            $this->view->getRenderingContext()->setControllerName('pages');
            $this->view->getRenderingContext()->setControllerAction(GeneralUtility::underscoredToLowerCamelCase($layout->getIdentifier()));
        }
    }

    /**
     * Set layout root path if given in configuration
     *
     * @param array $conf Configuration array
     */
    protected function setLayoutRootPath(array $conf): void
    {
        // Define the default root paths to be located in the base paths under "layouts/" subfolder
        $templateRootPaths = array_map(fn(string $path): string => $path . 'layouts/', $this->view->getTemplateRootPaths());
        // Override the default layout path via typoscript
        $layoutPaths = array_merge($templateRootPaths, $this->applyStandardWrapToFluidPaths($conf['layoutRootPaths.'] ?? []));
        if (!empty($layoutPaths)) {
            $this->view->setLayoutRootPaths($layoutPaths);
        }
    }

    /**
     * Set partial root path if given in configuration
     *
     * @param array $conf Configuration array
     */
    protected function setPartialRootPath(array $conf): void
    {
        // Define the default root paths to be located in the base paths under "partials/" subfolder
        $templateRootPaths = array_map(fn(string $path): string => $path . 'partials/', $this->view->getTemplateRootPaths());
        $partialPaths = array_merge($templateRootPaths, $this->applyStandardWrapToFluidPaths($conf['partialRootPaths.'] ?? []));
        if (!empty($partialPaths)) {
            $this->view->setPartialRootPaths($partialPaths);
        }
    }

    /**
     * Set different format if given in configuration
     */
    protected function setFormat(array $conf): void
    {
        $format = $this->cObj->stdWrapValue('format', $conf);
        if ($format) {
            $this->view->setFormat($format);
        }
    }

    /**
     * Compile rendered content objects in variables array ready to assign to the view
     *
     * @param array $conf Configuration array
     * @return array the variables to be assigned
     * @throws \InvalidArgumentException
     */
    protected function getContentObjectVariables(array $conf): array
    {
        $variables = [];
        // Accumulate the variables to be process and loop them through cObjGetSingle
        $variablesToProcess = (array)($conf['variables.'] ?? []);
        foreach ($variablesToProcess as $variableName => $cObjType) {
            if (is_array($cObjType)) {
                continue;
            }
            if (!in_array($variableName, $this->reservedVariables, true)) {
                $cObjConf = $variablesToProcess[$variableName . '.'] ?? [];
                $variables[$variableName] = $this->cObj->cObjGetSingle($cObjType, $cObjConf, 'variables.' . $variableName);
            } else {
                throw new \InvalidArgumentException(
                    'Cannot use reserved name "' . $variableName . '" as variable name in FLUIDTEMPLATE.',
                    1288095720
                );
            }
        }
        // These will be added later, once the page and all its relations is resolved
        // $variables['data'] = new Page($this->cObj->data);
        // $variables['current'] = $this->cObj->data[$this->cObj->currentValKey] ?? null;
        $variables['site'] = $this->request->getAttribute('site');
        $variables['language'] = $this->request->getAttribute('language');
        $variables['page'] = $this->request->getAttribute('frontend.page.information');
        return $variables;
    }

    /**
     * Set any TypoScript settings to the view.
     */
    protected function assignSettings(array $conf): void
    {
        $settingsFromPage = $this->request->getAttribute('frontend.typoscript')->getSettingsTree()->toArray();
        $settingsFromPage = $this->typoScriptService->convertTypoScriptArrayToPlainArray($settingsFromPage);
        $settings = [];
        if (isset($conf['settings.'])) {
            $settings = $this->typoScriptService->convertTypoScriptArrayToPlainArray($conf['settings.']);
        }
        $this->view->assign('settings', array_merge_recursive($settings, $settingsFromPage));
    }

    /**
     * Render fluid standalone view
     */
    protected function renderFluidView(): string
    {
        return $this->view->render();
    }

    /**
     * Apply standard wrap to content
     *
     * @param string $content Rendered HTML content
     * @param array $conf Configuration array
     * @return string Standard wrapped content
     */
    protected function applyStandardWrapToRenderedContent(string $content, array $conf): string
    {
        if (isset($conf['stdWrap.'])) {
            $content = $this->cObj->stdWrap($content, $conf['stdWrap.']);
        }
        return $content;
    }

    /**
     * Applies stdWrap on Fluid path definitions
     */
    protected function applyStandardWrapToFluidPaths(array $paths): array
    {
        $finalPaths = [];
        foreach ($paths as $key => $path) {
            if (str_ends_with((string)$key, '.')) {
                if (isset($paths[substr($key, 0, -1)])) {
                    continue;
                }
                $path = $this->cObj->stdWrap('', $path);
            } elseif (isset($paths[$key . '.'])) {
                $path = $this->cObj->stdWrap($path, $paths[$key . '.']);
            }
            $finalPaths[$key] = GeneralUtility::getFileAbsFileName($path);
        }
        return $finalPaths;
    }
}
