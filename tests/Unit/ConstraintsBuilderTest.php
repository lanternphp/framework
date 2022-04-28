<?php

namespace LanternTest\Unit;

use Lantern\Features\Constraints\ClassExists;
use Lantern\Features\Constraints\ExecutableIsInstalled;
use Lantern\Features\Constraints\ExtensionIsLoaded;
use Lantern\Features\ConstraintsBuilder;
use LanternTest\TestCase;

class ConstraintsBuilderTest extends TestCase
{
    /** @test */
    public function classExistsTest()
    {
        $constraint = new ClassExists(static::class);
        $this->assertTrue($constraint->isMet());

        $constraint = new ClassExists(static::class.'\ReallyIsNotGongToExist');
        $this->assertFalse($constraint->isMet());
    }

    /** @test */
    public function executableIsInstalledTest()
    {
        $constraint = new ExecutableIsInstalled('phpunit');
        $this->assertTrue($constraint->isMet());

        $constraint = new ExecutableIsInstalled('some_random_binary_that_surely_wont_exist_for_lantern_testing');
        $this->assertFalse($constraint->isMet());
    }

    /** @test */
    public function extensionLoadedTest()
    {
        $firstExtension = get_loaded_extensions()[0];
        $constraint = new ExtensionIsLoaded($firstExtension);
        $this->assertTrue($constraint->isMet());

        $constraint = new ExtensionIsLoaded('some_random_extension_that_surely_wont_exist_for_lantern_testing');
        $this->assertFalse($constraint->isMet());
    }

    /** @test */
    public function constraintsMetOnBuilderIfAllConstraintMet()
    {
        $firstExtension = get_loaded_extensions()[0];
        $secondExtension = get_loaded_extensions()[1];

        $builder = new ConstraintsBuilder();
        $builder->extensionIsLoaded($firstExtension);
        $builder->extensionIsLoaded($secondExtension);
        $builder->classExists(static::class);
        $builder->executableIsInstalled('phpunit');

        $this->assertTrue($builder->constraintsMet());
    }

    /** @test */
    public function constraintsnotMetOnBuilderIfAnyConstraintNotMet()
    {
        $firstExtension = get_loaded_extensions()[0];

        $builder = new ConstraintsBuilder();
        $builder->extensionIsLoaded($firstExtension);
        $builder->classExists(static::class);
        $builder->extensionIsLoaded('some_random_extension_that_surely_wont_exist_for_lantern_testing');
        $builder->executableIsInstalled('phpunit');

        $this->assertFalse($builder->constraintsMet());
    }
}