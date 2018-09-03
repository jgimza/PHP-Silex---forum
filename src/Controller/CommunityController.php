<?php
/**
 * Community controller.
 */
namespace Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Repository\CommunityRepository;
use Repository\RoleRepository;

/**
 * Class CommunityController.
 *
 * @package Controller
 */

class CommunityController implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
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

    public function viewAction(Application $app, $id)
    {
        $communityRepository = new CommunityRepository($app['db']);
        $roleRepository = new RoleRepository($app['db']);

        return $app['twig']->render(
            'community/view.html.twig',
            [
                'community' => $communityRepository->findData($id),
                'post' => $communityRepository->findUserPosts($id),
                'id' => $id,
                'adminrole' => $roleRepository->getAdminID(),
            ]
        );
    }
}