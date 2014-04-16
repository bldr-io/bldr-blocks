<?php

/**
 * This file is part of blocks.bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks;

use Aequasi\Environment\Environment;
use Silex\Provider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Console extends \Symfony\Component\Console\Application
{
    /**
     * @var Application $application
     */
    protected $application;

    const NAME = 'Bldr Blocks';

    const VERSION = '1.0.0';

    const LOGO = <<<EOL
  ______    __       _______   ______
 |   _  \  |  |     |       \ |   _  \
 |  |_)  | |  |     |  .--.  ||  |_)  |
 |   _  <  |  |     |  |  |  ||      /
 |  |_)  | |  `----.|  `--`  ||  |\  \
 |______/  |_______||_______/ | _| `._|
EOL;


    /**
     * {@inheritDoc}
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        parent::__construct(static::NAME, static::VERSION);
        $this->application['debug'] = $this->application->getEnvironment()->isDebug();
        $this->setDirectoryParameters();
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->application->boot();

        return parent::run($input, $output);
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getHelp()
    {
        return "\n\n\n" . static::LOGO . "\n\n\n\n" . parent::getHelp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $finder = new Finder();
        $finder->files()->in($this->application['src_dir'].'/Command/')->name('*.php')->notName('Abstract*');
        foreach ($finder as $file) {
            $class = $this->convertFileToNamespace($file);
            $command = new $class($this->application);
            $command->setApplication($this);
            $commands[] = $command;
        }

        return $commands;
    }

    /**
     * Sets the directory parameters
     */
    private function setDirectoryParameters()
    {
        $this->application['app_dir']    = realpath(__DIR__.'/../app');
        $this->application['cache_dir']  = realpath($this->application['app_dir'].'/cache');
        $this->application['logs_dir']   = realpath($this->application['app_dir'].'/logs');
        $this->application['config_dir'] = realpath($this->application['app_dir'].'/config');
        $this->application['src_dir']    = realpath(__DIR__);
    }

    /**
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function initialize()
    {
        $this->registerDatabase();
        $this->registerProviders();

        if ($this->application['debug']) {
            require $this->application['config_dir'].'/dev.php';
        } else {
            require $this->application['config_dir'].'/prod.php';
        }
    }

    /**
     * Sets up the Database Provider
     */
    private function registerDatabase()
    {
        $this->register(
            new Provider\DoctrineServiceProvider(),
            [
                'dbs.options' => Yaml::parse($this->application['config_dir'].'/databases.yml')
            ]
        );

        foreach ($this->application['dbs'] as $name => $db) {
            $this->application[$name.'_db'] = $db;
        }
    }

    /**
     * Registers the providers for this Console
     */
    private function registerProviders()
    {
        $this->register(new Provider\TwigServiceProvider());
        $this->register(new Provider\ServiceControllerServiceProvider());
        $this->register(new Provider\UrlGeneratorServiceProvider());

        if ($this->application['debug']) {
            $this->register(
                new Provider\MonologServiceProvider(),
                ['monolog.logfile' => $this->application['logs_dir'].'/development.log']
            );
        }
    }

    private function convertFileToNamespace(SplFileInfo $file)
    {
        $name = str_replace('.php', '', $file->getFilename());
        $name = sprintf(
            'Bldr\Blocks\Command\%s%s',
            $file->getRelativePath() === '' ? '' : str_replace('/', '\\', $file->getRelativePath()).'\\',
            $name
        );


        return $name;
    }
}
