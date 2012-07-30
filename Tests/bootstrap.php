<?php

if (!($loader = @include __DIR__ . '/../vendor/autoload.php')) {
    die(<<<'EOT'
You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install
EOT
    );
}

$bundleLoader = (function($class) {
    if (0 === strpos($class, 'SimpleThings\\FormSerializerBundle\\')) {
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
});
spl_autoload_register($bundleLoader);

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
Doctrine\Common\Annotations\AnnotationRegistry::registerLoader($bundleLoader);

