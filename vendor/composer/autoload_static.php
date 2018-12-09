<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4bd992f3fe1450dc79e6744f409417a4
{
    public static $files = array (
        'f44ebc90d80cbb579ada64e2a10f1bd9' => __DIR__ . '/../..' . '/helpers/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'System\\' => 7,
        ),
        'A' => 
        array (
            'Apps\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'System\\' => 
        array (
            0 => __DIR__ . '/../..' . '/system',
        ),
        'Apps\\' => 
        array (
            0 => __DIR__ . '/../..' . '/apps',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4bd992f3fe1450dc79e6744f409417a4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4bd992f3fe1450dc79e6744f409417a4::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}