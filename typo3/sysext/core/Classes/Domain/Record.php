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

namespace TYPO3\CMS\Core\Domain;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @internal not part of public API, as this needs to be streamlined and proven
 */
class Record implements \ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    protected array $properties = [];

    protected string $type = '';

    protected int $uid;
    protected int $pid;
    protected array $specialProperties = [];
    protected array $rawProperties = [];

    public static function createFromPreparedRecord(array $properties, string $type, array $specialProperties = []): self
    {
        $obj = new self();
        $obj->uid = (int)$properties['uid'];
        $obj->pid = (int)$properties['pid'];
        unset($properties['uid'], $properties['pid']);
        $obj->properties = $properties;
        $obj->type = $type;
        $obj->specialProperties = $specialProperties;
        return $obj;
    }

    public function withRawProperties(array $rawProperties): self
    {
        $obj = clone $this;
        $obj->rawProperties = $rawProperties;
        return $obj;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function getLanguageId(): int
    {
        return $this->specialProperties['language']['id'] ?? 0;
    }

    public function getTranslationParent(): int
    {
        return $this->specialProperties['language']['translationParent'] ?? 0;
    }

    public function getFullType(): string
    {
        return $this->type;
    }

    public function getType(): string
    {
        return GeneralUtility::revExplode('.', $this->type, 2)[1];
    }

    public function getRawProperties(): array
    {
        return $this->rawProperties;
    }

    public function toArray(bool $includeSpecialProperties = false): array
    {
        if ($includeSpecialProperties) {
            return $this->properties + $this->specialProperties;
        }
        return $this->properties;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->properties[$offset]) || isset($this->rawProperties[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->properties[$offset] ?? $this->rawProperties[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->properties[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->properties[$offset]);
        unset($this->rawProperties[$offset]);
    }

    public function getRawUid(): int
    {
        if (isset($this->rawProperties['_ORIG_uid'])) {
            return (int)$this->rawProperties['_ORIG_uid'];
        }
        if (isset($this->rawProperties['_LOCALIZED_UID'])) {
            return (int)$this->rawProperties['_LOCALIZED_UID'];
        }
        return $this->uid;
    }
}
