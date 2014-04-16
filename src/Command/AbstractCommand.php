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

use Bldr\Blocks\Application;
use Symfony\Component\Console\Command\Command;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var Application $application
     */
    protected $application;

    /**
     * {@inheritDoc}
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        parent::__construct(null);
    }
}
