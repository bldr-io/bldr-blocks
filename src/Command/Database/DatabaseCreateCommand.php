<?php

/**
 * This file is part of blocks.bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks\Command\Database;

use Bldr\Blocks\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\DriverManager;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class DatabaseCreateCommand extends AbstractCommand
{

    /**
     * {@inherotDoc}
     */
    protected function configure()
    {
        $this->setName('database:create')
            ->setDescription('Creates the configured databases')
            ->addArgument('connection', InputArgument::REQUIRED, 'Connection to use')
            ->setHelp(
                <<<EOF
The <info>doctrine:database:create</info> command creates the given
connections database:

<info>php app/console doctrine:database:create <connection></info>
EOF
            );
    }

    /**
     * {@inherotDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->application['dbs'][$input->getArgument('connection')];

        $params = $connection->getParams();
        $name = isset($params['path']) ? $params['path'] : $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);

        // Only quote if we don't have a path
        if (!isset($params['path'])) {
            $name = $tmpConnection->getDatabasePlatform()->quoteSingleIdentifier($name);
        }

        $error = false;
        try {
            $tmpConnection->getSchemaManager()->createDatabase($name);
            $output->writeln(sprintf('<info>Created database for connection named <comment>%s</comment></info>', $name));
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Could not create database for connection named <comment>%s</comment></error>', $name));
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $error = true;
        }

        $tmpConnection->close();

        return $error ? 1 : 0;
    }
}
