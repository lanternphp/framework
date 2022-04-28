<?php

namespace Lantern\Features;

use Lantern\Features\Constraints\ClassExists;
use Lantern\Features\Constraints\Constraint;
use Lantern\Features\Constraints\ExecutableIsInstalled;
use Lantern\Features\Constraints\ExtensionIsLoaded;

/**
 * ConstraintsBuilder is used to declare what system-level constraints are present for a feature.
 * E.g.
 * - a system binary must be findable on the path
 * - a particular function or class must be present
 * - the system must be installed on a particular OS
 */
class ConstraintsBuilder
{
    protected $constraints = [];

    /**
     * Have the declared constraints been met.
     *
     * @return bool
     */
    public function constraintsMet(): bool
    {
        $notMet = collect($this->constraints)->first(function (Constraint $constraint) {
            return !$constraint->isMet();
        });

        if ($notMet === null) {
            return true;
        }

        return false;
    }

    public function executableIsInstalled($executableName): self
    {
        $this->constraints[] = new ExecutableIsInstalled($executableName);

        return $this;
    }

    public function classExists($fullyQualifiedClassName): self
    {
        $this->constraints[] = new ClassExists($fullyQualifiedClassName);

        return $this;
    }

    public function extensionIsLoaded($extensionName): self
    {
        $this->constraints[] = new ExtensionIsLoaded($extensionName);

        return $this;
    }
}
