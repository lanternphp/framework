<?php

namespace Lantern\Features\Constraints;

class ExtensionIsLoaded implements Constraint
{
    protected $extensionName;

    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    public function isMet(): bool
    {
        if (!extension_loaded($this->extensionName)) {
            return false;
        }

        return true;
    }

    public function description(): string
    {
        return sprintf('Extension "%s" must be loaded', $this->extensionName);
    }
}
