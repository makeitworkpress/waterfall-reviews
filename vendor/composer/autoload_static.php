<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3868b7f0c51dabb62d6ac9492737c2aa
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit3868b7f0c51dabb62d6ac9492737c2aa::$classMap;

        }, null, ClassLoader::class);
    }
}
