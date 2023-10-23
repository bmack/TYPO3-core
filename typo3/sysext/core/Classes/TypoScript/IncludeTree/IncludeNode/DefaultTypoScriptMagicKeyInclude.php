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

namespace TYPO3\CMS\Core\TypoScript\IncludeTree\IncludeNode;

/**
 * A node created for "magic" include from globals, when processing
 * $GLOBALS['TYPO3_CONF_VARS ']['FE']['defaultTypoScript_[constants|setup]']
 *
 * @internal: Internal tree structure.
 */
final class DefaultTypoScriptMagicKeyInclude extends AbstractInclude {}
