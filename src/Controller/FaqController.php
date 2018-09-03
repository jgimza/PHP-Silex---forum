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
     * {@inheritdoc}
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('faq_index');
        return $controller;
    }

    public function indexAction(Application $app)
    {
        return $app['twig']->render(
            'faq/index.html.twig'
        );
    }
}