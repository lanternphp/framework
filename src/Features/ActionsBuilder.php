<?php

namespace Lantern\Features;

use Illuminate\Support\Str;
use Lantern\LanternException;

class ActionsBuilder
{
    /**
     * @var string|Feature
     */
    protected $feature;

    protected $actions = [];

    public function __construct($feature)
    {
        $this->feature = $feature;
    }

    /**
     * @param array|Action[] $actions
     * @throws LanternException
     */
    public function declare(array $actions, string $stack)
    {
        foreach ($actions as $action) {
            $actionId = $action::id();

            if (Str::contains($actionId, '.')) {
                throw LanternException::actionIdInvalid('contains a dot', $actionId);
            }

            $this->actions[$actionId] = $action;
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return $this->actions;
    }

    /**
     * @return Feature|string
     */
    public function feature()
    {
        return $this->feature;
    }
}
