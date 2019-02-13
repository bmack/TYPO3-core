<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Authentication\Event;

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

/**
 * Event which is fired when a failed login was attempted.
 * Can be used to implement login failure tracking methods.
 *
 * Possibly defines whether sleep() should be called later-on, defaults to 5
 */
class FailedLoginEvent extends UserAuthenticationEvent
{
    /**
     * If no listeners altered this, then the default login failure behavior is to sleep for 5 seconds
     * @var int
     */
    private $sleep = 5;

    public function sleep(int $sleep)
    {
        return $this->sleep = $sleep;
    }

    public function getSleepTime(): int
    {
        return $this->sleep;
    }
}
