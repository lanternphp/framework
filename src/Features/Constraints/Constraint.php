<?php

namespace Lantern\Features\Constraints;

interface Constraint
{
    /**
     * Check whether the conditions of a constraint have been met.
     *
     * @return bool
     */
    public function isMet(): bool;

    /**
     * Provide a human readable description of the constraint.
     *
     * @return string
     */
    public function description(): string;
}
