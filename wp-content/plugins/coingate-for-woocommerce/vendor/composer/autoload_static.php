<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3fa5b128251386d090218c9e9c1fdf6d
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CoinGate\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CoinGate\\' => 
        array (
            0 => __DIR__ . '/..' . '/coingate/coingate-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3fa5b128251386d090218c9e9c1fdf6d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3fa5b128251386d090218c9e9c1fdf6d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3fa5b128251386d090218c9e9c1fdf6d::$classMap;

        }, null, ClassLoader::class);
    }
}
