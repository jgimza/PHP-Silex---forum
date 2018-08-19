<?php
/**
 * Tag controller.
 */
namespace Controller;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Form\ForumType;
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
        $controller->get('/{id}', [$this, 'viewAction'])
                ->assert('id', '[1-9][0-9]*')
                ->bind('topic_view');
        $controller->match('/add', [$this, 'addAction'])
                ->method('GET|POST')
                ->bind('topic_add');
        $controller->match('/edit/{id}', [$this, 'editAction'])
            ->assert('id', '[1-9][0-9]*')
            ->method('GET|POST')
            ->bind('topic_edit');
        $controller->get('/delete/{id}', [$this, 'deleteAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('topic_delete');

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
                'slug' => $slug,
                'section' => $topicRepository->findSectionName($slug)
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

    public function addAction(Application $app, $slug, Request $request)
    {
        $form = $app['form.factory']->createBuilder(ForumType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['idForumSection'] = $slug;
            $data['idForumUser'] = $this->getUserID($app);

            $topicRepository = new TopicRepository($app['db']);
            $topicRepository->add($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.add',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        return $app['twig']->render(
            'topic/add.html.twig',
            [
                'form' => $form->createView(),
                'slug' => $slug,
            ]
        );
    }

    public function editAction(Application $app, $slug, $id, Request $request)
    {
        $topicRepository = new TopicRepository($app['db']);
        $data = $topicRepository->findOneById($id);

        $form = $app['form.factory']->createBuilder(ForumType::class, $data)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $topicRepository = new TopicRepository($app['db']);
            $topicRepository->edit($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.edit',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        return $app['twig']->render(
            'topic/edit.html.twig',
            [
                'form' => $form->createView(),
                'slug' => $slug,
                'id' => $id,
            ]
        );
    }

    public function deleteAction(Application $app, $slug, $id)
    {
        $topicRepository = new TopicRepository($app['db']);
        $topicRepository->delete($id);

        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    private function getUserID(Application $app)
    {
        $login = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userRepository = new UserRepository($app['db']);
        return $userRepository->getUserByLogin($login)['idForumUser'];
    }
}

