<?php
/**
 * Topic controller.
 */
namespace Controller;

use Repository\UserRepository;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Form\ForumType;
use Form\PostType;
use Form\TopicType;
use Form\SubmitType;
use Repository\TopicRepository;

/**
 * Class TopicController.
 *
 */

class TopicController implements ControllerProviderInterface
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
        $controller->get('/', [$this, 'indexAction'])->bind('topic_index');
        $controller->match('/{id}', [$this, 'viewAction'])
                ->method('GET|POST')
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
        $controller->match('/close/{id}', [$this, 'closeTopicAction'])
            ->assert('id', '[1-9][0-9]*')
            ->method('GET|POST')
            ->bind('closesubmit');
        $controller->match('/submit/{id}', [$this, 'submitAction'])
            ->assert('id', '[1-9][0-9]*')
            ->method('GET|POST')
            ->bind('topicsubmit');

        return $controller;
    }

    /**
     * Index action.
     *
     * @param Application $app
     *
     * @param int         $slug
     *
     * @return mixed
     */
    public function indexAction(Application $app, $slug)
    {
        $topicRepository = new TopicRepository($app['db']);
        $topic = $topicRepository->findOneById($slug);

        if (!$topic) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $blocked = -1;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
        }

        return $app['twig']->render(
            'topic/index.html.twig',
            [
                'posts' => $topicRepository->findNofPosts($slug),
                'slug' => $slug,
                'section' => $topicRepository->findSectionName($slug),
                'isblocked' => $blocked,
            ]
        );
    }

    /**
     * View action.
     *
     * @param Application $app
     *
     * @param int         $slug
     *
     * @param int         $id
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewAction(Application $app, $slug, $id, Request $request)
    {
        $topicRepository = new TopicRepository($app['db']);
        $topic = $topicRepository->findOneById($id);

        if (!$topic) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $form = $app['form.factory']->createBuilder(PostType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['idForumTopic'] = $id;
            $data['idForumUser'] = $this->getUserID($app);

            $postRepository = new PostRepository($app['db']);
            $postRepository->add($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.add',
                ]
            );

            return $app->redirect($app['url_generator']->generate('topic_view', array('id' => $id, 'slug' => $slug)));
        }

        $userID = -1;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userID = $this->getUserID($app);
        }

        $blocked = -1;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
        }

        return $app['twig']->render(
            'topic/view.html.twig',
            [
                'form' => $form->createView(),
                'topic' => $topicRepository->findPostData($id),
                'posts' => $topicRepository->findUserPosts(),
                'open' => $topicRepository->findIfOpen($id),
                'isblocked' => $blocked,
                'currentuserid' => $userID,
                'slug' => $slug,
                'id' => $id,
            ]
        );
    }

    /**
     * Submit post delete operation action.
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $slug
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function submitAction(Application $app, Request $request, $slug, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $topicRepository = new TopicRepository($app['db']);

        $form = $app['form.factory']->createBuilder(SubmitType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['submit'] === true) {
                $topicRepository->delete($id);
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.submit',
                    ]
                );
            } else {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'danger',
                        'message' => 'message.notsubmit',
                    ]
                );
            }

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        return $app['twig']->render(
            'topic/topicsubmit.html.twig',
            [
                'form' => $form->createView(),
                'slug' => $slug,
                'id' => $id,
            ]
        );
    }

    /**
     * Close topic action.
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $slug
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closeTopicAction(Application $app, Request $request, $slug, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }
        $topicRepository = new TopicRepository($app['db']);
        $form = $app['form.factory']->createBuilder(SubmitType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['submit'] === true) {
                $topicRepository->closeTopic($id);
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'success',
                        'message' => 'message.submit',
                    ]
                );
            } else {
                $app['session']->getFlashBag()->add(
                    'messages',
                    [
                        'type' => 'danger',
                        'message' => 'message.notsubmit',
                    ]
                );
            }

            return $app->redirect($app['url_generator']->generate('topic_view', array('id' => $id, 'slug' => $slug)));
        }

        return $app['twig']->render(
            'topic/closesubmit.html.twig',
            [
                'form' => $form->createView(),
                'slug' => $slug,
                'id' => $id,
            ]
        );
    }

    /**
     * Add topic action.
     *
     * @param Application $app
     *
     * @param int         $slug
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Application $app, $slug, Request $request)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $form = $app['form.factory']->createBuilder(ForumType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['idForumSection'] = $slug;
            $data['idForumUser'] = $this->getUserID($app);

            $post['content'] = $data['content'];
            unset($data['content']);
            $topicRepository = new TopicRepository($app['db']);

            $topicRepository->add($data);
            $post['idForumTopic'] = $app['db']->lastInsertId();
            $post['idForumUser'] = $data['idForumUser'];
            $postRepository = new PostRepository($app['db']);
            $postRepository->add($post);

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

    /**
     * Edit topic name action.
     *
     * @param Application $app
     *
     * @param int         $slug
     *
     * @param int         $id
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Application $app, $slug, $id, Request $request)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $topicRepository = new TopicRepository($app['db']);
        $data = $topicRepository->findOneById($id);

        $form = $app['form.factory']->createBuilder(TopicType::class, $data)->getForm();
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

    /**
     * Delete topic action.
     *
     * @param Application $app
     *
     * @param int         $slug
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Application $app, $slug, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $topicRepository = new TopicRepository($app['db']);
        $topicRepository->delete($id);

        return $app->redirect($app['url_generator']->generate('homepage'));
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
