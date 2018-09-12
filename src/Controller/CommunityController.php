<?php
/**
 * Community controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\CommunityRepository;
use Form\PrivilegeType;
use Form\BlockType;
use Repository\RoleRepository;
use Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommunityController.
 *
 */

class CommunityController implements ControllerProviderInterface
{

    /**
     * Routing settings
     *
     * @param Application $app
     *
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('community_index');
        $controller->get('/{id}', [$this, 'viewAction'])->bind('community_view');
        $controller->match('/blockuser/{id}', [$this, 'blockuserAction'])
            ->assert('id', '[1-9][0-9]*')
            ->method('GET|POST')
            ->bind('blockuser');
        $controller->match('/giveprivilege/{id}', [$this, 'giveprivilegeAction'])
            ->assert('id', '[1-9][0-9]*')
            ->method('GET|POST')
            ->bind('giveprivilege');

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
        $communityRepository = new CommunityRepository($app['db']);

        return $app['twig']->render(
            'community/index.html.twig',
            [
                'communities' => $communityRepository->findAll(),
            ]
        );
    }

    /**
     * View action
     *
     * @param Application $app
     *
     * @param int         $id
     *
     * @return mixed
     */
    public function viewAction(Application $app, $id)
    {
        $communityRepository = new CommunityRepository($app['db']);
        $community = $communityRepository->findOneById($id);
        if (!$community) {
            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $roleRepository = new RoleRepository($app['db']);

        $userID = -1;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userID = $this->getUserID($app);
        }

        $blocked = -1;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
        }

        return $app['twig']->render(
            'community/view.html.twig',
            [
                'community' => $communityRepository->findData($id),
                'post' => $communityRepository->findUserPosts($id),
                'id' => $id,
                'adminrole' => $roleRepository->getAdminID(),
                'current' => $userID,
                'isblocked' => $blocked,
            ]
        );
    }

    /**
     * Give admin or user privileges.
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function giveprivilegeAction(Application $app, Request $request, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $current = $this->getUserID($app);
        if ($id === $current) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'danger',
                    'message' => 'message.impossible',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }

        $role = $this->getRoles($app);

        $form = $app['form.factory']->createBuilder(PrivilegeType::class, $role)->getForm();
            $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            unset($data['role']);

            $data['idForumUser'] = $id;
            $userRepository = new UserRepository($app['db']);

            $userRepository->update($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                'type' => 'success',
                'message' => 'message.add',
                ]
            );
        }

        return $app['twig']->render(
            'community/giveprivilege.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
            ]
        );
    }

    /**
     * Block or unblock user action.
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function blockuserAction(Application $app, Request $request, $id)
    {

        // Check if user is blocked
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if (0 === $blocked) {
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $current = $this->getUserID($app);
        if ($id === $current) {
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'danger',
                    'message' => 'message.impossible',
                ]
            );

            return $app->redirect($app['url_generator']->generate('homepage'));
        }


        $form = $app['form.factory']->createBuilder(BlockType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $data['idForumUser'] = $id;
            $userRepository = new UserRepository($app['db']);

            $userRepository->update($data);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.add',
                ]
            );
        }

        return $app['twig']->render(
            'community/blockuser.html.twig',
            [
                'form' => $form->createView(),
                'id' => $id,
            ]
        );
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

    /**
     * Get all user roles as dictionary.
     *
     * @param Application $app
     *
     * @return array
     */
    private function getRoles(Application $app)
    {
        $roleRepository = new RoleRepository($app['db']);

        $role = $roleRepository->getAll();

        $data = [];
        foreach ($role as $rola) {
            $data['role'][$rola['name']] = $rola['idForumUserRole'];
        }

        return $data;
    }
}
