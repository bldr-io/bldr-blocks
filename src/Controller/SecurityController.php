<?php

/**
 * This file is part of content.videosz.com
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Blocks\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class SecurityController
{
    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function loginAction(Application $app, Request $request)
    {
        return $app['twig']->render(
            'Security/login.html.twig',
            [
                'error'         => $app['security.last_error']($request),
                'last_username' => $app['session']->get('_security.last_username'),
            ]
        );
    }
}
