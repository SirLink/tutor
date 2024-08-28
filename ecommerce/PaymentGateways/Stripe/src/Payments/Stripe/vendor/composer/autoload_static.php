<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7098137cffcc8125ae02cc7a994a5da
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'O' => 
        array (
            'Ollyo\\PaymentHub\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'Ollyo\\PaymentHub\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7098137cffcc8125ae02cc7a994a5da::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7098137cffcc8125ae02cc7a994a5da::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb7098137cffcc8125ae02cc7a994a5da::$classMap;

        }, null, ClassLoader::class);
    }
}
