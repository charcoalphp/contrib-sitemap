<?php

namespace Charcoal\Sitemap;

use Charcoal\App\Module\AbstractModule;
use Charcoal\Sitemap\ServiceProvider\SitemapServiceProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Sitemap Module
 */
class SitemapModule extends AbstractModule
{
    /**
     * Path to the admin configuration for sitemap generation.
     *
     * Include this file from your application config, for example:
     *
     * ```php
     * $config->addFile(\Charcoal\Sitemap\SitemapModule::ADMIN_CONFIG);
     * ```
     */
    public const ADMIN_CONFIG = 'vendor/charcoal/contrib-sitemap/config/admin.json';

    /**
     * Setup the module's dependencies.
     *
     * @return self
     */
    public function setup()
    {
        /** @var \Pimple\Container $container */
        $container = $this->app()->getContainer();

        if (PHP_SAPI === 'cli') {
            $this->setupScriptRoutes();
        } else {
            $this->setupPublicRoutes();
        }

        $sitemapServiceProvider = new SitemapServiceProvider();
        $container->register($sitemapServiceProvider);

        return $this;
    }

    /**
     * Register the 'sitemap.xml' route.
     *
     * Sitemap.xml is generated on the fly by the SitemapAction controller.
     * If a static `www/sitemap.xml` file already exists, the web server may
     * serve it instead of hitting this route.
     *
     * @return void
     */
    private function setupPublicRoutes()
    {
        $config = [
            'route'      => '/sitemap.xml',
            'methods'    => [ 'GET' ],
            'controller' => 'charcoal/sitemap/action/sitemap',
            'ident'      => 'charcoal/sitemap/action/sitemap',
        ];

        $container = $this->app()->getContainer();

        $this->app()->map($config['methods'], $config['route'], function (
            RequestInterface $request,
            ResponseInterface $response,
            array $args = []
        ) use (
            $config,
            $container
        ) {
            $routeControllerClass = $this['route/controller/action/class'];

            $routeController = $container['route/factory']->create($routeControllerClass, [
                'config' => $config,
                'logger' => $this['logger'],
            ]);

            return $routeController($this, $request, $response);
        });
    }

    /**
     * Register the `/sitemap/generate` CLI route.
     *
     * Invoked as `vendor/bin/charcoal sitemap/generate`. The sitemap should be
     * regenerated when content is updated (e.g. via cron).
     *
     * @return void
     */
    private function setupScriptRoutes()
    {
        $config = [
            'route'      => '/sitemap/generate',
            'methods'    => [ 'GET' ],
            'controller' => 'charcoal/sitemap/script/generate-sitemap',
            'ident'      => 'charcoal/sitemap/script/generate-sitemap',
        ];

        $container = $this->app()->getContainer();

        $this->app()->map($config['methods'], $config['route'], function (
            RequestInterface $request,
            ResponseInterface $response,
            array $args = []
        ) use (
            $config,
            $container
        ) {
            $routeControllerClass = $this['route/controller/script/class'];

            $routeController = $container['route/factory']->create($routeControllerClass, [
                'config' => $config,
                'logger' => $this['logger'],
            ]);

            return $routeController($this, $request, $response);
        });
    }
}
