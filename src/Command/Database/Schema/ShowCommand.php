<?php

/**
 * This file is part of blocks.bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks\Command\Database\Schema;

use Bldr\Blocks\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class ShowCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('database:schema:show')
            ->setDescription('Output Schema Declaration')
            ->addArgument('connection', InputArgument::REQUIRED, 'Database to use');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $file = sprintf(
            "%s/Resources/database/%s.php",
            $this->application['src_dir'],
            $input->getArgument('connection')
        );

        $schema = require $file;
        $connection = $this->application['dbs'][$input->getArgument('connection')];

        $allSql = '';
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $allSql .= $sql.';';
        }

        $output->writeln(\SqlFormatter::format($allSql));
    }
}
