<?php
/**
 * Community controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\CommunityRepository;
use Repository\RoleRepository;
use Repository\UserRepository;

/**
 * Class CommunityController.
 *
 * @package Controller
 */

class CommunityController implements ControllerProviderInterface
{

    /**
     * Routing settings
     *
     * @param Application $app
     * @return mixed
     */

    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/', [$this, 'indexAction'])->bind('community_index');
        $controller->get('/{id}', [$this, 'viewAction'])->bind('community_view');
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
     * @param int $id
     * @return mixed
     */

    public function viewAction(Application $app, $id)
    {
        $communityRepository = new CommunityRepository($app['db']);
        $roleRepository = new RoleRepository($app['db']);

        $userID = -1;
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userID = $this->getUserID($app);
        }

        $blocked = -1;
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
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
     * Get currently logged in user id.
     *
     * @param Application $app
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
     * @return mixed
     */

    private function isBlocked(Application $app)
    {
        $login = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userRepository = new UserRepository($app['db']);
        return $userRepository->getUserByLogin($login)['blocked'];
    }
}