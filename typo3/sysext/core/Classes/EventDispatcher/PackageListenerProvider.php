<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\EventDispatcher;

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

use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides Listeners from all installed TYPO3 Extensions
 * found in EXT:my_extension/Configuration/EventListeners.php
 */
class PackageListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array
     */
    protected $listeners;

    public function __construct(
        PackageManager $packageManager,
        FrontendInterface $coreCache,
        DependencyOrderingService $dependencyOrderingService
    ) {
        // See if the Routes.php from all active packages have been built together already
        $cacheIdentifier = 'EventDispatcher_' . sha1(TYPO3_version . Environment::getProjectPath() . 'PackageListenerProvider');

        if ($coreCache->has($cacheIdentifier)) {
            // substr is necessary, because the php frontend wraps php code around the cache value
            $eventListeners = json_decode(substr($coreCache->get($cacheIdentifier), 6, -2), true);
        } else {
            $eventListeners = [];
            $unorderedEventListeners = [];
            // Loop over all packages and check for a Configuration/EventListener.php file
            $packages = $packageManager->getActivePackages();
            foreach ($packages as $package) {
                $listenerFileForPackage = $package->getPackagePath() . 'Configuration/EventListeners.php';
                if (file_exists($listenerFileForPackage)) {
                    $definedEventListenersInPackage = require $listenerFileForPackage;
                    if (is_array($definedEventListenersInPackage)) {
                        $unorderedEventListeners = array_merge($unorderedEventListeners, $definedEventListenersInPackage);
                    }
                }
            }
            // Sort them
            foreach ($unorderedEventListeners as $eventName => $listeners) {
                $listeners = $dependencyOrderingService->orderByDependencies($listeners);
                $listeners = array_column($listeners, 'listener');
                $eventListeners[$eventName] = $listeners;
            }

            // Store the data from all packages in the cache
            $coreCache->set($cacheIdentifier, json_encode($eventListeners));
        }
        $this->listeners = $eventListeners;
    }

    /**
     * Not part of the public API, only used for debugging purposes
     *
     * @internal
     */
    public function getAllListenerDefinitions(): array
    {
        return $this->listeners;
    }

    /**
     * @inheritdoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        $className = get_class($event);
        if (isset($this->listeners[$className])) {
            foreach ($this->listeners[$className] as $listener) {
                yield $this->getCallableFromTarget($listener);
            }
        }
        $classParents = class_parents($className);
        foreach ($classParents as $classParent) {
            if (isset($this->listeners[$classParent])) {
                foreach ($this->listeners[$classParent] as $listener) {
                    yield $this->getCallableFromTarget($listener);
                }
            }
        }
        $classContracts = class_implements($className);
        foreach ($classContracts as $classContract) {
            if (isset($this->listeners[$classContract])) {
                foreach ($this->listeners[$classContract] as $listener) {
                    yield $this->getCallableFromTarget($listener);
                }
            }
        }
    }

    /**
     * Creates a callable out of the given parameter, which can be a string, a callable / closure or an array
     *
     * @param array|string|callable $target the target which is being resolved.
     * @return callable|object
     * @throws \InvalidArgumentException
     */
    protected function getCallableFromTarget($target)
    {
        if (is_array($target)) {
            return $target;
        }

        if (is_object($target) && $target instanceof \Closure) {
            return $target;
        }

        // Only a class name is given
        if (is_string($target) && strpos($target, ':') === false) {
            $targetObject = GeneralUtility::makeInstance($target);
            if (!method_exists($targetObject, '__invoke')) {
                throw new \InvalidArgumentException('Object "' . $target . '" doesn\'t implement an __invoke() method and cannot be used as target.', 1549988531);
            }
            return $targetObject;
        }

        // Check if the target is a concatenated string of "className::actionMethod"
        if (is_string($target) && strpos($target, '::') !== false) {
            list($className, $methodName) = explode('::', $target, 2);
            $targetObject = GeneralUtility::makeInstance($className);
            return [$targetObject, $methodName];
        }

        // Closures needs to be checked at last as a string with object::method is recognized as callable
        if (is_callable($target)) {
            return $target;
        }

        throw new \InvalidArgumentException('Invalid target for "' . $target . '", as it is not callable.', 1549988537);
    }
}
