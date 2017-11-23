<?php

namespace kuiper\boot\providers;

use kuiper\boot\Provider;
use kuiper\di;
use kuiper\web\exception\ViewException;
use kuiper\web\TwigView;
use kuiper\web\ViewInterface;

class TwigViewProvider extends Provider
{
    public function register()
    {
        $this->services->addDefinitions([
            ViewInterface::class => di\object(TwigView::class),
            \Twig_Environment::class => di\factory([$this, 'provideTwig']),
        ]);
    }

    public function provideTwig()
    {
        $settings = $this->settings;
        try {
            $loader = new \Twig_Loader_Filesystem($settings['app.views_path']);
        } catch (\Twig_Error $e) {
            throw new ViewException($e->getMessage(), $e->getCode(), $e);
        }
        $options = [];
        if (!$settings['app.dev_mode'] && $settings['app.runtime_path']) {
            $cacheDir = $settings['app.runtime_path'].'/views_cache';
            if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true)) {
                throw new ViewException("Cannot create twig cache dir '$cacheDir'");
            }
            $options['cache'] = $cacheDir;
        }
        $twig = new \Twig_Environment($loader, $options);
        if ($baseUri = $settings['app.static_base_uri']) {
            $twig->addFunction(new \Twig_SimpleFunction('static_url', function ($path) use ($baseUri) {
                return $baseUri.$path;
            }));
        }
        foreach ($this->app->getModules() as $module) {
            $path = $settings[$module->getName().'.views_path'];
            if ($path) {
                $loader->addPath($path, $module->getName());
            }
        }

        return $twig;
    }
}
