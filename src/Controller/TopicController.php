<?php
/**
 * Tag controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\TopicRepository;

/**
 * Class TagController.
 *
 * @package Controller
 */
class TopicController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('topic_index');
        $controller->get('/{id}', [$this, 'viewAction'])->bind('topic_view');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */
    public function indexAction(Application $app, $slug)
    {
        $topicRepository = new TopicRepository($app['db']);

        return $app['twig']->render(
            'topic/index.html.twig',
            [
                'posts' => $topicRepository->findNofPosts($slug),
            ]
        );
    }

    public function viewAction(Application $app, $id)
    {
        $topicRepository = new TopicRepository($app['db']);

        return $app['twig']->render(
            'topic/view.html.twig',
            [
                'topic' => $topicRepository->findPostData($id),
                'posts' => $topicRepository->findUserPosts()
            ]
        );
    }
}

