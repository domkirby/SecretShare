<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1c8a1ef3d6be7e51a0a9b9c991ff9fc9
{
    public static $prefixesPsr0 = array (
        'B' => 
        array (
            'Bramus' => 
            array (
                0 => __DIR__ . '/..' . '/bramus/router/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit1c8a1ef3d6be7e51a0a9b9c991ff9fc9::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit1c8a1ef3d6be7e51a0a9b9c991ff9fc9::$classMap;

        }, null, ClassLoader::class);
    }
}
