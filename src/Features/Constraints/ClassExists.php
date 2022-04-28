<?php

namespace Lantern\Features\Constraints;

class ClassExists implements Constraint
{
    protected $fullyQualifiedClassName;

    public function __construct($fullyQualifiedClassName)
    {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
    }

    public function isMet(): bool
    {
        if (class_exists($this->fullyQualifiedClassName, true)) {
            return true;
        }

        return false;
    }

    public function description(): string
    {
        return sprintf('Class "%s" must exist or be loadable with an autoloader', $this->fullyQualifiedClassName);
    }
}
