<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite655cf1fdd106dfabc17297e248ce748
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Chieff\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Chieff\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Chieff',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite655cf1fdd106dfabc17297e248ce748::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite655cf1fdd106dfabc17297e248ce748::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite655cf1fdd106dfabc17297e248ce748::$classMap;

        }, null, ClassLoader::class);
    }
}