<?php

namespace Lantern\Features;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Lantern\LanternException;

/**
 * The ActionProxy can call an action and check if the action is available.
 */
class ActionProxy
{
    /**
     * @var Action
     */
    protected $action;

    /**
     * @var Response|null
     */
    protected $available;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this, $name.'Proxy'], $arguments);
    }

    protected function prepareProxy(...$args): ActionResponse
    {
        if ($this->available === null) {
            $this->available();
        }

        if ($this->available->denied()) {
            throw LanternException::actionNotAvailable($this->action::id(), $this->available->message());
        }

        if (! method_exists($this->action, 'prepare')) {
            throw LanternException::actionMethodMissing($this->action::id(), 'prepare');
        }

        return call_user_func_array([$this->action, 'prepare'], $args);
    }

    protected function performProxy(...$args): ActionResponse
    {
        if ($this->available === null) {
            $this->available();
        }

        if ($this->available->denied()) {
            throw LanternException::actionNotAvailable($this->action::id(), $this->available->message());
        }

        if (! method_exists($this->action, 'perform')) {
            throw LanternException::actionMethodMissing($this->action::id(), 'perform');
        }

        return $this->action->perform(...$args);
    }

    /**
     * @param Authorizable|null $user
     * @return false|Response
     * @throws LanternException
     */
    public function checkAvailabilityThroughGate(Authorizable $user = null)
    {
        $features = FeatureRegistry::featuresForAction($this->action);

        foreach ($features as $feature) {
            if (! $feature->constraintsMet()) {
                return $this->available = false;
            }
        }

        $user = $this->getUser($user);

        return $this->available = $this->action->checkAvailability($user);
    }

    public function available($user = null): bool
    {
        $user = $this->getUser($user);

        $check = app(GateContract::class)->forUser($user)->allows($this->action::id(), [$this]);
        if (is_bool($check)) {
            $check = $check ? Response::allow() : Response::deny();
        }

        $this->available = $check;

        return $this->available->allowed();
    }

    protected function getUser($user = null)
    {
        $user = $user ?? app('auth')->user();

        if (!$user && $this->action::GUEST_USERS) {
            $user = app('auth.driver')->getProvider()->createModel();
        }

        return $user;
    }

    protected function getGuard()
    {
        // todo - add in ability to specify user guard for an action
        return app('auth');
    }
}
