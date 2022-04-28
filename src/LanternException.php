<?php

namespace Lantern;

/**
 * @method static LanternException alreadySetup(...$extraInfo)
 * @method static LanternException featureNotFound(...$extraInfo)
 * @method static LanternException featureAlreadyDeclared(...$extraInfo)
 * @method static LanternException featureNotExtendingBase(...$extraInfo)
 * @method static LanternException featureEmpty(...$extraInfo)
 * @method static LanternException subFeatureCannotDeclareStack(...$extraInfo)
 * @method static LanternException actionNotDeclared(...$extraInfo)
 * @method static LanternException actionAlreadyDeclared(...$extraInfo)
 * @method static LanternException actionNotAvailable(...$extraInfo)
 * @method static LanternException actionMethodMissing(...$extraInfo)
 * @method static LanternException actionIdInvalid(...$extraInfo)
 */
class LanternException extends \Exception
{
    protected static $errors = [
        // configuration errors
        'alreadySetup'                 => [1, 'Setup has already been called'],

        // 1xx feature errors
        'featureNotFound'              => [100, 'Feature class not found'],
        'featureAlreadyDeclared'       => [101, 'Feature already declared with this ID'],
        'featureNotExtendingBase'      => [102, 'Feature does not extend the base Feature class'],
        'featureEmpty'                 => [103, 'Feature does not contain any other Feature or Action'],
        'subFeatureCannotDeclareStack' => [104, 'A stack can only be set on the top-most feature'],

        // 2xx action errors
        'actionNotDeclared'            => [200, 'Action has not been declared by a Feature'],
        'actionAlreadyDeclared'        => [201, 'Action already declared with this ID'],
        'actionNotAvailable'           => [202, 'Action not available'],
        'actionMethodMissing'          => [203, 'Action method missing'],
        'actionIdInvalid'              => [204, 'Action ID is invalid'],
    ];

    public static function make($error, $extraInfo = null)
    {
        if (!array_key_exists($error, static::$errors)) {
            $error = 'unknown';
        }

        $code = static::$errors[$error][0];
        $message = static::$errors[$error][1];

        if ($extraInfo) {
            $message .= " ($extraInfo)";
        }

        return new self("Lantern: $message", $code);
    }

    public static function __callStatic($method, $arguments)
    {
        if (!array_key_exists($method, static::$errors)) {
            $method = 'unknown';
        }

        $extraInfo = implode('| ', $arguments);

        return static::make($method, $extraInfo);
    }
}
