<?php

/**
 * This file is part of content.videosz.com
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

$this['twig.path']    = $this['src_dir'] . '/Resources/views';
$this['twig.options'] = ['cache' => $this['cache_dir'] . '/twig'];
