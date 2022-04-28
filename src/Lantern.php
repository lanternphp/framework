<?php

namespace Lantern;

use Lantern\Features\FeatureRegistry;

class Lantern
{
    /**
     * @var string[] an array of additional directories to use when searching the
     */
    protected static $pathDirs = [];

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
}
