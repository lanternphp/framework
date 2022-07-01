<?php

# expanded the namespace to offer protection for utility classes below
namespace LanternTest\Unit\ActionsTest;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Lantern\Features\Action;
use Lantern\Features\AvailabilityBuilder;
use Lantern\Features\ConstraintsBuilder;
use Lantern\Features\Feature;
use Lantern\Lantern;
use Lantern\LanternException;
use LanternTest\TestCase;

class ActionsTest extends TestCase
{
    /** @test */
    public function theIdOfAnActionCannotContainAFullStop()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionMessage('Action ID is invalid');

        Lantern::setUp(FeatureWithInvalidAction::class);
    }

    /** @test */
    public function anActionIsUnavailableIfItsConstraintsFail()
    {
        Lantern::setUp(AllFeatures::class);
        $this->assertFalse(ActionWithFailingConstraint::make()->available());
    }

    /** @test */
    public function anActionIsAvailableIfItsConstraintsPass()
    {
        Lantern::setUp(AllFeatures::class);
        $this->assertTrue(ActionWithPassingConstraint::make()->available());
    }

    /** @test */
    public function anActionIsUnavailableIfItsAvailabilityFails()
    {
        Lantern::setUp(AllFeatures::class);
        $this->assertFalse(ActionWithFailingAvailability::make()->available());
    }

    /** @test */
    public function anActionIsAvailableIfItsAvailabilityPasses()
    {
        Lantern::setUp(AllFeatures::class);
        $this->assertTrue(ActionWithPassingAvailability::make()->available());
    }

    /** @test */
    public function availabilityCanBeCheckedThroughAGateInLaravel()
    {
        Lantern::setUp(AllFeatures::class);
        /** @var GateContract $gate */
        $gate = app(GateContract::class);
        $this->assertTrue($gate->check('action-with-passing-availability'));
    }

    /** @test */
    public function availabilityCanBeCheckedForDifferentUsers()
    {
        Lantern::setUp(AllFeatures::class);
        /** @var GateContract $gate */
        $gate = app(GateContract::class);

        $user = app('auth.driver')->getProvider()->createModel();
        $user->id = 1;

        // this should now cause the above availability check to return false
        $this->assertFalse(
            $gate->forUser($user)->check('action-with-passing-availability')
        );
    }

    /** @test */
    public function availabilityBuilderCanBeOverriddenWithAChildClass()
    {
        Lantern::setUp(AllFeatures::class);
        Lantern::useCustomAvailabilityBuilder(CustomAvailabilityBuilder::class);
        $this->assertTrue(ActionUsingCustomAvailabilityBuilder::make()->available());
    }

    /** @test */
    public function cannotCallPrepareOnAnActionProxyWhenNotDeclaredOnTheAction()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionCode(203);

        Lantern::setUp(FeatureWithMissingMethods::class);
        ActionMissingMethods::make()->prepare();
    }

    /** @test */
    public function cannotCallPerformOnAnActionProxyWhenNotDeclaredOnTheAction()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionCode(203);

        Lantern::setUp(FeatureWithMissingMethods::class);
        ActionMissingMethods::make()->perform();
    }
}


class ActionWithInvalidId extends Action
{
    const GUEST_USERS = true;
    const ID = 'my.id';
}

class FeatureWithInvalidAction extends Feature
{
    const ACTIONS = [
        ActionWithInvalidId::class,
    ];
}

class ActionWithFailingConstraint extends Action
{
    const GUEST_USERS = true;

    protected function constraints(ConstraintsBuilder $constraints)
    {
        $constraints->extensionIsLoaded('some_random_extension_that_surely_wont_exist_for_lantern_testing');
    }
}

class ActionWithPassingConstraint extends Action
{
    const GUEST_USERS = true;

    protected function constraints(ConstraintsBuilder $constraints)
    {
        $firstExtension = get_loaded_extensions()[0];
        $constraints->extensionIsLoaded($firstExtension);
    }
}

class ActionWithFailingAvailability extends Action
{
    const GUEST_USERS = true;

    protected function availability(AvailabilityBuilder $availabilityBuilder)
    {
        $user = $availabilityBuilder->user();
        $availabilityBuilder->assertNotNull($user->id);
    }
}

class ActionWithPassingAvailability extends Action
{
    const GUEST_USERS = true;

    protected function availability(AvailabilityBuilder $availabilityBuilder)
    {
        $user = $availabilityBuilder->user();
        $availabilityBuilder->assertNull($user->id);
    }
}

class ActionUsingCustomAvailabilityBuilder extends Action
{
    const GUEST_USERS = true;

    /**
     * @param CustomAvailabilityBuilder|AvailabilityBuilder $availabilityBuilder
     * @return void
     */
    protected function availability(AvailabilityBuilder $availabilityBuilder)
    {
        $availabilityBuilder->assertHappy('happy');
    }
}

class AllFeatures extends Feature
{
    const ACTIONS = [
        ActionWithFailingConstraint::class,
        ActionWithPassingConstraint::class,
        ActionWithFailingAvailability::class,
        ActionWithPassingAvailability::class,
        ActionUsingCustomAvailabilityBuilder::class,
    ];
}

class ActionMissingMethods extends Action
{
    const GUEST_USERS = true;
}

class FeatureWithMissingMethods extends Feature
{
    const ACTIONS = [
        ActionMissingMethods::class,
    ];
}

class CustomAvailabilityBuilder extends AvailabilityBuilder
{
    public function assertHappy($value, $failureMessage = 'value passed to `assertHappy` is sad'): self
    {
        $this->checks[] = function () use ($value, $failureMessage): Response {
            if ($value == 'happy') {
                return Response::allow();
            }

            return Response::deny($failureMessage);
        };

        return $this;
    }
}