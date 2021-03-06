<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Routing\Enhancers;

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

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Resolves a static list (like page.typeNum) against a file pattern. Usually added on the very last part
 * of the URL.
 *
 * type: PageTypeEnhancer
 *   routePath: '{type}'
 *   requirements:
 *     type: '.json|.html'
 *
 */
class PageTypeEnhancer
{
    protected $configuration;

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Used when a URL is matched.
     * @param RouteCollection $collection
     */
    public function addVariants(RouteCollection $collection)
    {
        foreach ($collection->all() as $existingRoute) {
            $variant = clone $existingRoute;
            $variant->setPath(rtrim($variant->getPath(), '/') . $this->configuration['routePath']);
            $variant->addDefaults(['type' => 0]);
            $variant->addRequirements($this->configuration['requirements'] ?? ['type' => '.*']);
            $collection->add('enhancer_' . spl_object_hash($this) . spl_object_hash($existingRoute), $variant);
        }
    }

    /**
     * @param Route $route
     * @return Route
     */
    public function enhanceDefaultRoute(Route $route)
    {
        $route->setPath(rtrim($route->getPath(), '/') . $this->configuration['routePath']);
        $route->addDefaults(['type' => 0]);
        $route->addRequirements($this->configuration['requirements']);
        return $route;
    }

    public function flattenParameters($parameters) {
        return $parameters;
    }

    public function unflattenParameters($parameters) {
        return $parameters;
    }
}
