<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit728cb20bf5f2ba84885f409a0fd997ce
{
    public static $prefixLengthsPsr4 = array (
        'a' => 
        array (
            'adriangibbons\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'adriangibbons\\' => 
        array (
            0 => __DIR__ . '/..' . '/adriangibbons/php-fit-file-analysis/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit728cb20bf5f2ba84885f409a0fd997ce::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit728cb20bf5f2ba84885f409a0fd997ce::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit728cb20bf5f2ba84885f409a0fd997ce::$classMap;

        }, null, ClassLoader::class);
    }
}
