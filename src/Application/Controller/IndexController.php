<?php

namespace Application\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Borut Balažek <bobalazek124@gmail.com>
 */
class IndexController
{
    public function indexAction(Request $request, Application $app)
    {
        return new Response(
            $app['twig']->render(
                'contents/index.html.twig'
            )
        );
    }
}
