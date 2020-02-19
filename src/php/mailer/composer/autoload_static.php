<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc649d9b5c334e3a13539ece80163b416
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc649d9b5c334e3a13539ece80163b416::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc649d9b5c334e3a13539ece80163b416::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
