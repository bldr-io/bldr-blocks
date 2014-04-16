<?php

/**
 * This file is part of blocks.bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class CacheClearCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription('Clears the cache');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = $this->getApplication()['cache_dir'];
        $finder = Finder::create()->in($cacheDir)->notName('.gitkeep');

        $filesystem = new Filesystem();
        $filesystem->remove($finder);

        $output->writeln(sprintf("%s <info>success</info>", 'cache:clear'));
    }
}
