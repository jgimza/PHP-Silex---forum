<?php
/**
 * Faq controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;

/**
 * Class FaqController.
 *
 * @package Controller
 */

class FaqController implements ControllerProviderInterface
{

    /**
     * Routing settings.
     *
     * @param Application $app
     * @return mixed
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('faq_index');
        return $controller;
    }

    /**
     * Index action.
     *
     * @param Application $app
     * @return mixed
     */

    public function indexAction(Application $app)
    {
        return $app['twig']->render(
            'faq/index.html.twig'
        );
    }
}