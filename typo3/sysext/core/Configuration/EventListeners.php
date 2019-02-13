<?php
/**
 * An array consisting of implementations of PSR-14 event listeners to be registered
 *
 *  'Eventidentifier' => [
 *      'listener-identifier (optional)' => [
 *         'listener' => classname::method or callable
 *         'before/after' => array of dependencies
 *      ]
 *   ]
 */
return [
    \TYPO3\CMS\Core\Authentication\Event\AfterUserLookUpEvent::class => [
        'legacy-hook' => [
            'listener' => \TYPO3\CMS\Core\Compatibility\HookListeners::class . '::onAbstractUserAuthenticationPostUserLookUp',
            'before' => ['first']
        ],
    ],
    \TYPO3\CMS\Core\Authentication\Event\BeforeLogoutEvent::class => [
        'legacy-hook' => [
            'listener' => \TYPO3\CMS\Core\Compatibility\HookListeners::class . '::onAbstractUserAuthenticationLogoffPreProcessing',
            'before' => ['first']
        ],
    ],
    \TYPO3\CMS\Core\Authentication\Event\AfterLogoutEvent::class => [
        'legacy-hook' => [
            'listener' => \TYPO3\CMS\Core\Compatibility\HookListeners::class . '::onAbstractUserAuthenticationLogoffPostProcessing',
            'before' => ['first']
        ],
    ],
    \TYPO3\CMS\Core\Authentication\Event\FailedLoginEvent::class => [
        'legacy-hook' => [
            'listener' => \TYPO3\CMS\Core\Compatibility\HookListeners::class . '::onAbstractUserAuthenticationPostLoginFailureProcessing',
            'before' => ['first']
        ],
    ],
];
