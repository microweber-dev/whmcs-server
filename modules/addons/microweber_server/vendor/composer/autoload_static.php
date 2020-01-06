<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit868bcf7b6f9ca3478602d520a65d3f1a
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MicroweberServer\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MicroweberServer\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit868bcf7b6f9ca3478602d520a65d3f1a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit868bcf7b6f9ca3478602d520a65d3f1a::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}