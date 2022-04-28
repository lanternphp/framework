<?php

namespace Lantern\Features;

trait HasSystemConstraintsTrait
{
    /**
     * Override this static method in your Feature to add system-level constraints that
     * must be met for this feature to be available.
     *
     * @param ConstraintsBuilder $constraints
     */
    protected function constraints(ConstraintsBuilder $constraints)
    {
    }

    final public function constraintsMet(): bool
    {
        static $constraintsMet;

        $k = static::class;

        $constraintsMet[$k] = $constraintsMet[$k] ?? null;

        if ($constraintsMet[$k] === null) {
            $constraints = new ConstraintsBuilder();

            $this->constraints($constraints);
            $constraintsMet[$k] = $constraints->constraintsMet();
        }

        return $constraintsMet[$k];
    }
}
