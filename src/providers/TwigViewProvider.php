<?php

namespace kuiper\boot\providers;

use kuiper\di;
use kuiper\boot\Provider;
use kuiper\web\ViewInterface;
use kuiper\web\TwigView;

class TwigViewProvider extends Provider
{
    public function register()
    {
        $this->services->addDefinitions([
            ViewInterface::class => di\object(TwigView::class),
            \Twig_Environment::class => di\factory([$this, 'provideTwigView'])
        ]);
    }

    public function provideTwigView()
    {
        $loader = new \Twig_Loader_Filesystem($this->settings['app.views_path']);
        return $twig = new \Twig_Environment($loader, array(
            'cache' => $this->settings['app.views_cache_path'] ?: $this->settings['app.runtime_path'].'/views'
        ));
    }
}
