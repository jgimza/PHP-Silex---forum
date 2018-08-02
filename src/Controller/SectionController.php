<?php
/**
 * Tag controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\SectionRepository;

/**
 * Class TagController.
 *
 * @package Controller
 */
class SectionController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('', [$this, 'indexAction'])->bind('section_index');
        $controller->get('{id}', [$this, 'viewAction'])->bind('section_view');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app)
    {
        $sectionRepository = new SectionRepository($app['db']);


        return $app['twig']->render(
            'index.html.twig',
            [
                'sections' => $sectionRepository->findAll(),
            ]
        );
    }

    public function viewAction(Application $app, $id)
    {
        $sectionRepository = new SectionRepository($app['db']);

        return $app['twig']->render(
            'section/view.html.twig',
            [

                'section' => $sectionRepository->findOneById($id),
            ]
        );
    }

}