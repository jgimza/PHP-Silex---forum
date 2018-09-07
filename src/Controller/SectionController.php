<?php
/**
 * Section controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\SectionRepository;

/**
 * Class SectionController.
 *
 * @package Controller
 */

class SectionController implements ControllerProviderInterface
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
        $controller->get('', [$this, 'indexAction'])->bind('section_index');
        $controller->get('{id}', [$this, 'viewAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('section_view');
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
        $sectionRepository = new SectionRepository($app['db']);

        return $app['twig']->render(
            'index.html.twig',
            [
                'sections' => $sectionRepository->findAll(),
            ]
        );
    }

    /**
     * View action.
     *
     * @param Application $app
     * @param int $id
     * @return mixed
     */

    public function viewAction(Application $app, $id)
    {
        $sectionRepository = new SectionRepository($app['db']);
        dump($sectionRepository->findAll());

        return $app['twig']->render(
            'section/view.html.twig',
            [
                'section' => $sectionRepository->findOneById($id),
            ]
        );
    }
}