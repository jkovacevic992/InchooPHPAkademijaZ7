<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4b737eae23bf8d09eb767d6796992d44
{
    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'Metzli' => 
            array (
                0 => __DIR__ . '/..' . '/z38/metzli/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit4b737eae23bf8d09eb767d6796992d44::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
