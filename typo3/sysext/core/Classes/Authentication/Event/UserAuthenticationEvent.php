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

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

/**
 * Generic event for everything that is related to UserAuthentication
 */
class UserAuthenticationEvent
{
    /**
     * @var AbstractUserAuthentication
     */
    private $userAuthentication;

    public function __construct(AbstractUserAuthentication $userAuthentication)
    {
        $this->userAuthentication = $userAuthentication;
    }

    public function getUserAuthentication(): AbstractUserAuthentication
    {
        return $this->userAuthentication;
    }
}
