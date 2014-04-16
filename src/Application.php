<?php

/**
 * This file is part of content.videosz.com
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks;

use Aequasi\Environment\Environment;
use Knp\Menu\MenuItem;
use nymo\Silex\Provider\BreadCrumbServiceProvider;
use nymo\Twig\Extension\BreadCrumbExtension;
use Silex\Provider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Application extends \Silex\Application
{
    /**
     * @var Environment $environment
     */
    protected $environment;

    /**
     * {@inheritDoc}
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;

        parent::__construct();

        $this->setDirectoryParameters();

        $this['debug'] = $this->environment->isDebug();
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Sets the directory parameters
     */
    private function setDirectoryParameters()
    {
        $this['app_dir']    = realpath(__DIR__ . '/../app');
        $this['web_dir']    = realpath(__DIR__ . '/../web');
        $this['cache_dir']  = realpath($this['app_dir'] . '/cache');
        $this['logs_dir']   = realpath($this['app_dir'] . '/logs');
        $this['config_dir'] = realpath($this['app_dir'] . '/config');
        $this['src_dir']    = realpath(__DIR__);
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function initialize()
    {
        $this->registerDatabase();
        $this->registerProviders();
        $this->buildSecurity();
        $this->loadRoutes();
        $this->createErrorHandler();

        if ($this['debug']) {
            require $this['config_dir'] . '/dev.php';
        } else {
            require $this['config_dir'] . '/prod.php';
        }
    }

    private function registerDatabase()
    {
        $this->register(
            new Provider\DoctrineServiceProvider(),
            [
                'dbs.options' => Yaml::parse($this['config_dir'] . '/databases.yml')
            ]
        );

        foreach ($this['dbs'] as $name => $db) {
            $this[$name.'_db'] = $db;
        }
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    private function registerProviders()
    {
        $this->register(new Provider\TwigServiceProvider());
        $this->register(new Provider\ServiceControllerServiceProvider());
        $this->register(new Provider\UrlGeneratorServiceProvider());

        $this->register(new BreadCrumbServiceProvider());
        $this['twig'] = $this->share(
            $this->extend(
                'twig',
                function ($twig, $app) {
                    $twig->addExtension(new BreadCrumbExtension($app));

                    return $twig;
                }
            )
        );

        $this->register(new Provider\SecurityServiceProvider());
        $this->register(new Provider\SessionServiceProvider());

        if ($this['debug']) {
            $this->register(
                new Provider\MonologServiceProvider(),
                ['monolog.logfile' => $this['logs_dir'] . '/development.log']
            );

            $p = new Provider\WebProfilerServiceProvider();
            $this->register($p, ['profiler.cache_dir' => $this['cache_dir'] . '/profiler']);
            $this->mount('/_profiler', $p);
        }
    }

    /**
     *
     */
    private function buildSecurity()
    {
        $this['security.firewalls'] = Yaml::parse($this['config_dir'] . '/security.yml');
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function loadRoutes()
    {
        $this['routes'] = $this->extend(
            'routes',
            function (RouteCollection $routes, Application $app) {
                $loader     = new YamlFileLoader(new FileLocator($app['src_dir'] . '/Resources/config'));
                $collection = $loader->load('routes.yml');
                $routes->addCollection($collection);

                return $routes;
            }
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function createErrorHandler()
    {
        $app = $this;
        $this->error(
            function (\Exception $e, $code) use ($app) {
                switch ($code) {
                    case 404:
                        $this['breadcrumbs']->addItem('404 Error', '#');

                        return $this['twig']->render('Error/error-404.html.twig');
                    default:
                        $this['breadcrumbs']->addItem('500 Error', '#');

                        return $this['twig']->render('Error/error-500.html.twig', ['exception' => $e]);
                }
            }
        );
    }
}
