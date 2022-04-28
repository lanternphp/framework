<?php

namespace LanternTest\Unit;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Lantern\Features\Action;
use Lantern\Features\AvailabilityBuilder;
use LanternTest\TestCase;

class AvailabilityBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(GateContract::class)->define('allows-all', function ($user = null, ...$args) {
            return true;
        });

        app(GateContract::class)->define('denies-all', function ($user = null, ...$args) {
            return false;
        });
    }

    protected function getUser(): AuthenticatableContract
    {
        return app('auth.driver')->getProvider()->createModel();
    }

    /** @test */
    public function canCheckAnAuthorisationGateFromLaravel()
    {
        $action = new class () extends Action {
            const ID = 'my-action';
        };
        $user = $this->getUser();

        $builder = new AvailabilityBuilder($action, $user);
        $builder->userCan('allows-all');

        $this->assertTrue($builder->checksMet()->allowed());

        $builder->userCan('denies-all');

        $checksMet = $builder->checksMet();
        $this->assertFalse($checksMet->allowed());
        $this->assertStringContainsString('some checks failed', $checksMet->message());
    }

    /** @test */
    public function canCheckAVarietyOfAssertions()
    {
        $action = new class () extends Action {
            const ID = 'my-action';
        };
        $user = $this->getUser();

        $builder = new AvailabilityBuilder($action, $user);
        $builder->assertTrue(true);
        $builder->assertFalse(false);
        $builder->assertNull(null);
        $builder->assertNotNull(false);
        $builder->assertEmpty([]);
        $builder->assertNotEmpty([1]);
        $builder->assertEqual(1, '1');
        $builder->assertNotEqual(1, 2);

        $this->assertTrue($builder->checksMet()->allowed());

        $builder->assertTrue(false);

        $checksMet = $builder->checksMet();
        $this->assertFalse($checksMet->allowed());
        $this->assertStringContainsString('some checks failed', $checksMet->message());
    }

    /** @test */
    public function aMessageCanBePassedToEachAssertionToBeUsedToTellUsWhatFailed()
    {
        $action = new class () extends Action {
            const ID = 'my-action';
        };
        $user = $this->getUser();

        $builder = new AvailabilityBuilder($action, $user);
        $builder->assertTrue(true, 'User is great');
        $builder->assertTrue(false, 'User does not belong to this company');
        $builder->assertFalse(true, 'User not a super-user');

        $checksMet = $builder->checksMet();
        $this->assertFalse($checksMet->allowed());

        $this->assertStringNotContainsString('User is great', $checksMet->message()); // not ins message because check passed
        $this->assertStringContainsString('User does not belong to this company', $checksMet->message());
        $this->assertStringContainsString('User not a super-user', $checksMet->message());
    }
}