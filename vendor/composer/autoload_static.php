<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit040bb64e0655d2af87d7daa074ff1809
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'L' => 
        array (
            'Lcobucci\\JWT\\' => 13,
        ),
        'C' => 
        array (
            'CardinalCommerce\\Payments\\Objects\\' => 34,
            'CardinalCommerce\\Payments\\Interfaces\\' => 37,
            'CardinalCommerce\\Payments\\Credentials\\' => 38,
            'CardinalCommerce\\Payments\\Carts\\WooCommerce\\' => 44,
            'CardinalCommerce\\Payments\\Carts\\Common\\' => 39,
            'CardinalCommerce\\Client\\' => 24,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Lcobucci\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/lcobucci/jwt/src',
        ),
        'CardinalCommerce\\Payments\\Objects\\' => 
        array (
            0 => __DIR__ . '/..' . '/cardinalcommerce/client/src/CardinalCommerce/Payments/Objects',
        ),
        'CardinalCommerce\\Payments\\Interfaces\\' => 
        array (
            0 => __DIR__ . '/..' . '/cardinalcommerce/client/src/CardinalCommerce/Payments/Interfaces',
        ),
        'CardinalCommerce\\Payments\\Credentials\\' => 
        array (
            0 => __DIR__ . '/..' . '/cardinalcommerce/client/src/CardinalCommerce/Payments/Credentials',
        ),
        'CardinalCommerce\\Payments\\Carts\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/../..' . '/php-src/CardinalCommerce/Payments/Carts/WooCommerce',
        ),
        'CardinalCommerce\\Payments\\Carts\\Common\\' => 
        array (
            0 => __DIR__ . '/..' . '/cardinalcommerce/cart-common/src/CardinalCommerce/Payments/Carts/Common',
        ),
        'CardinalCommerce\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/cardinalcommerce/client/src/CardinalCommerce/Client',
        ),
    );

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'Httpful' => 
            array (
                0 => __DIR__ . '/..' . '/nategood/httpful/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit040bb64e0655d2af87d7daa074ff1809::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit040bb64e0655d2af87d7daa074ff1809::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit040bb64e0655d2af87d7daa074ff1809::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}