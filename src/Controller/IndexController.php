<?php

/**
 * This file is part of blocks.bldr.io
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class IndexController
{
    /**
     * @param Application $app
     *
     * @return Response
     */
    public function dashboardAction(Application $app)
    {
        $app['breadcrumbs']->addItem('Dashboard', $app['url_generator']->generate('dashboard'));
        return $app['twig']->render('Index/dashboard.html.twig');
    }
}
