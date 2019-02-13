<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Compatibility;

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

use TYPO3\CMS\Core\Authentication\Event\AfterLogoutEvent;
use TYPO3\CMS\Core\Authentication\Event\AfterUserLookUpEvent;
use TYPO3\CMS\Core\Authentication\Event\BeforeLogoutEvent;
use TYPO3\CMS\Core\Authentication\Event\FailedLoginEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides a replacement for all existing hooks in TYPO3 Core, which now act as a simple wrapper
 * for PSR-14 events with a simple ("first prioritized") listener implementation.
 */
class HookListeners
{
    public function onAbstractUserAuthenticationPostUserLookUp(AfterUserLookUpEvent $evt)
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] ?? [] as $funcName) {
            $_params = [
                'pObj' => $evt->getUserAuthentication(),
            ];
            GeneralUtility::callUserFunction($funcName, $_params, $evt->getUserAuthentication());
        }
        return $evt;
    }

    public function onAbstractUserAuthenticationLogoffPreProcessing(BeforeLogoutEvent $evt)
    {
        $_params = [];
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'] ?? [] as $_funcRef) {
            if ($_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $evt->getUserAuthentication());
            }
        }
        return $evt;
    }

    public function onAbstractUserAuthenticationLogoffPostProcessing(AfterLogoutEvent $evt)
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'] ?? [] as $_funcRef) {
            if ($_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $evt->getUserAuthentication());
            }
        }
        return $evt;
    }

    public function onAbstractUserAuthenticationPostLoginFailureProcessing(FailedLoginEvent $evt)
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postLoginFailureProcessing'] ?? [] as $_funcRef) {
            GeneralUtility::callUserFunction($_funcRef, $_params, $evt->getUserAuthentication());
            $evt->sleep(0);
        }
        return $evt;
    }
}
