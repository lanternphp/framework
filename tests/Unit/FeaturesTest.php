<?php

# expanded the namespace to offer protection for utility classes below
namespace LanternTest\Unit\FeaturesTest;

use Lantern\Features\Action;
use Lantern\Features\ConstraintsBuilder;
use Lantern\Features\Feature;
use Lantern\Features\FeatureRegistry;
use Lantern\Lantern;
use Lantern\LanternException;
use LanternTest\TestCase;

class FeaturesTest extends TestCase
{
    /** @test */
    public function aFeatureMustExtendTheBaseFeatureClassAndHaveOtherFeaturesOrActions()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionMessage('does not extend the base Feature class');

        Lantern::setUp(BadFeatureNoBaseClass::class);
    }

    /** @test */
    public function featureWithinAFeatureMustExist()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionMessage('Feature class not found');

        Lantern::setUp(BadFeaturePointingToUnknownFeature::class);
    }

    /** @test */
    public function aFeatureCannotBeEmpty()
    {
        $this->expectException(LanternException::class);
        $this->expectExceptionMessage('Feature does not contain any other Feature or Action');

        Lantern::setUp(BadFeatureEmpty::class);
    }

    /** @test */
    public function aFeatureCanDeclareActions()
    {
        Lantern::setUp(GoodFeatureWithAction::class);
        $features = FeatureRegistry::featuresForAction(new GoodAction);

        $this->assertCount(1, $features);
        $this->assertTrue(in_array(new GoodFeatureWithAction, $features));
    }

    /** @test */
    public function aFeatureCanOtherFeaturesWithActions()
    {
        Lantern::setUp(GoodFeatureWithAnotherFeature::class);
        $features = FeatureRegistry::featuresForAction(new GoodAction);

        $this->assertCount(2, $features);
        $this->assertTrue(in_array(new GoodFeatureWithAction, $features));
        $this->assertTrue(in_array(new GoodFeatureWithAnotherFeature, $features));
    }

    /** @test */
    public function aFeatureWithAFailingConstraintStopsTheAvailabilityOfAnyRelatedActions()
    {
        Lantern::setUp(GoodFeatureWithActionButFailingConstraint::class);
        $this->assertFalse(AnotherGoodAction::make()->available());
    }

    /** @test */
    public function aFeatureWithAPassingConstraintDoesNotStopTheAvailabilityOfAnyRelatedActions()
    {
        Lantern::setUp(GoodFeatureWithAction::class);
        $this->assertTrue(GoodAction::make()->available());
    }
}

class BadFeatureNoBaseClass
{

}

class BadFeaturePointingToUnknownFeature extends Feature
{
    const FEATURES = [
        'sfsdfdfsdkjfhsakdfhkjfhakljfhdkghdkjfh',
    ];
}

class BadFeatureEmpty extends Feature
{

}

class GoodAction extends Action
{
    const GUEST_USERS = true;
}

class AnotherGoodAction extends Action
{
    const GUEST_USERS = true;
}

class GoodFeatureWithAction extends Feature
{
    protected function constraints(ConstraintsBuilder $constraints)
    {
        $firstExtension = get_loaded_extensions()[0];
        $constraints->extensionIsLoaded($firstExtension);
    }

    const ACTIONS = [
        GoodAction::class,
    ];
}

class GoodFeatureWithActionButFailingConstraint extends Feature
{
    protected function constraints(ConstraintsBuilder $constraints)
    {
        $constraints->extensionIsLoaded('some_random_extension_that_surely_wont_exist_for_lantern_testing');
    }

    const ACTIONS = [
        AnotherGoodAction::class,
    ];
}

class GoodFeatureWithAnotherFeature extends Feature
{
    const FEATURES = [
        GoodFeatureWithAction::class,
    ];
}
