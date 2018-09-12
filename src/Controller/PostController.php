<?php
/**
 * Post controller.
 */
namespace Controller;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Repository\TopicRepository;
use Form\PostType;

/**
 * Class PostController.
 *
 */

class PostController implements ControllerProviderInterface
{

    /**
     * Routing settings.
     *
     * @param Application $app
     *
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->match('/edit/{id}', [$this, 'editAction'])
            ->method('GET|POST')
            ->assert('id', '[1-9][0-9]*')
            ->bind('post_edit');
        $controller->get('/delete/{id}', [$this, 'deleteAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('post_delete');

        return $controller;
    }

    /**
     * Edit post action.
     *
     * @param Application $app
     *
     * @param int         $id
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Application $app, $id, Request $request)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $postRepository = new PostRepository($app['db']);
        $topicRepository = new TopicRepository($app['db']);
        $data = $postRepository->findOneById($id);

        if ($data['idForumUser'] !== $this->getUserID($app)) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $topic = $topicRepository->findOneById($data['idForumTopic']);
        $slug = $topic['idForumSection'];

        if ($topic['open'] === 0) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $form = $app['form.factory']->createBuilder(PostType::class, $data)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $postRepository->edit($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.edit',
                ]
            );

            return $app->redirect($app['url_generator']->generate('topic_view', array('id' => $data['idForumTopic'], 'slug' => $slug)));
        }

        return $app['twig']->render(
            'post/edit.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
            ]
        );
    }

    /**
     * Delete post action.
     *
     * @param Application $app
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Application $app, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $postRepository = new PostRepository($app['db']);
        $topicRepository = new TopicRepository($app['db']);
        $data = $postRepository->findOneById($id);


        if (!$app['security.authorization_checker']->isGranted('ROLE_ADMIN') && $data['idForumUser'] !== $this->getUserID($app)) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $topic = $topicRepository->findOneById($data['idForumTopic']);
        $slug = $topic['idForumSection'];

        if ($topic['open'] === 0) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $postRepository->delete($data['idForumPost']);
        if ($topicRepository->findPostData($data['idForumTopic'])) {
            return $app->redirect($app['url_generator']->generate('topic_view', array('id' => $data['idForumTopic'], 'slug' => $slug)));
        }
        $topicRepository->delete($id);

        $app['session']->getFlashBag()->add(
            'messages',
            [
                'type' => 'success',
                'message' => 'message.edit',
            ]
        );

        return $app->redirect($app['url_generator']->generate('topic_index', array('slug' => $slug)));
    }

    /**
     * Get currently logged in user id.
     *
     * @param Application $app
     *
     * @return mixed
     */
    private function getUserID(Application $app)
    {
        $login = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userRepository = new UserRepository($app['db']);

        return $userRepository->getUserByLogin($login)['idForumUser'];
    }

    /**
     * Find if currently logged in user is blocked.
     *
     * @param Application $app
     *
     * @return mixed
     */
    private function isBlocked(Application $app)
    {
        $login = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userRepository = new UserRepository($app['db']);

        return $userRepository->getUserByLogin($login)['blocked'];
    }
}
