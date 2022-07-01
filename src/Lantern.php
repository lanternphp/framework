<?php

namespace Lantern;

use Lantern\Features\AvailabilityBuilder;
use Lantern\Features\FeatureRegistry;

class Lantern
{
    /**
     * @var string[] an array of additional directories to use when searching the
     */
    protected static $pathDirs = [];

    /**
     * @var string a class to use as a custom availability builder
     */
    protected static $customAvailabilityBuilder = null;

    /**
     * @param array|null $dirs
     * @return array
     */
    public static function pathDirs(array $dirs = null): array
    {
        if (is_array($dirs)) {
            return static::$pathDirs = $dirs;
        }

        $defaults = [
            base_path(),
            base_path('vendor/bin'),
        ];

        return array_merge(self::$pathDirs, $defaults);
    }

    /**
     * @throws LanternException
     */
    public static function setUp(string $group)
    {
        FeatureRegistry::register($group);
    }

    /**
     * @return string
     */
    public static function availabilityBuilder(): string
    {
        return static::$customAvailabilityBuilder ?? AvailabilityBuilder::class;
    }

    /**
     * @param string $avilabilityBuilder
     */
    public static function useCustomAvailabilityBuilder(string $avilabilityBuilder)
    {
        static::$customAvailabilityBuilder = $avilabilityBuilder;
    }
}
