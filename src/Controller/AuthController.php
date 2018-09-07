<?php
/**
 * Auth controller.
 *
 */
namespace Controller;

use Form\LoginType;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Form\RegisterType;
use Form\ChangepassType;
use Repository\UserRepository;
use Repository\RoleRepository;

/**
 * Class AuthController.
 */

class AuthController implements ControllerProviderInterface
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
        $controller->match('login', [$this, 'loginAction'])
            ->method('GET|POST')
            ->bind('auth_login');
        $controller->get('logout', [$this, 'logoutAction'])
            ->bind('auth_logout');
        $controller->match('register', [$this, 'registerAction'])
            ->method('GET|POST')
            ->bind('register');
        $controller->match('changepass', [$this, 'changepassAction'])
            ->method('GET|POST')
            ->bind('changepass');
        $controller->get('makeadmin/{id}', [$this, 'makeadminAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('makeadmin');
        $controller->get('makeuser/{id}', [$this, 'makeuserAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('makeuser');
        $controller->get('blockuser/{id}', [$this, 'blockuserAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('blockuser');
        $controller->get('unblockuser/{id}', [$this, 'unblockuserAction'])
            ->assert('id', '[1-9][0-9]*')
            ->bind('unblockuser');
        return $controller;
    }

    /**
     * Login action.
     *
     * @param Application $app
     * @param Request $request
     * @return mixed
     */

    public function loginAction(Application $app, Request $request)
    {
        $user = ['login' => $app['session']->get('_security.last_username')];
        $form = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();

        return $app['twig']->render(
            'auth/login.html.twig',
            [
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }

    /**
     * Logout action.
     *
     * @param Application $app
     * @return mixed
     */

    public function logoutAction(Application $app)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.html.twig', []);
    }

    /**
     * Change password action.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function changepassAction(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder(ChangepassType::class, [])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $data['idForumUser'] = $this->getUserID($app);
            $data['password'] = $app['security.encoder.bcrypt']->encodePassword($data['password'], '');
            $userRepository = new UserRepository($app['db']);
            $userRepository->update($data);

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
            'auth/changepass.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Give admin privileges action.
     *
     * @param Application $app
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function makeadminAction(Application $app, $id)
    {

        // Check if user is blocked
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if($blocked == 0){
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $roleRepository = new RoleRepository($app['db']);
        $data = [];
        $data['idForumUser'] = $id;
        $data['idForumUserRole'] = $roleRepository->getAdminID();
        $userRepository = new UserRepository($app['db']);
        $userRepository->update($data);
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    /**
     * Give user privileges action.
     *
     * @param Application $app
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function makeuserAction(Application $app, $id)
    {

        // Check if user is blocked
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if($blocked == 0){
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $roleRepository = new RoleRepository($app['db']);
        $data = [];
        $data['idForumUser'] = $id;
        $current = $this->getUserID($app);
        if ($data['idForumUser'] === $current){
            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $data['idForumUserRole'] = $roleRepository->getUserID();
        $userRepository = new UserRepository($app['db']);
        $userRepository->update($data);
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    /**
     * Block user account action.
     *
     * @param Application $app
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function blockuserAction(Application $app, $id)
    {

        // Check if user is blocked
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if($blocked == 0){
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $userRepository = new UserRepository($app['db']);
        $data = [];
        $data['idForumUser'] = $id;
        $current = $this->getUserID($app);
        if ($data['idForumUser'] === $current){
            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $data['blocked'] = 0;
        $userRepository->update($data);
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    /**
     * Unblock user account action.
     *
     * @param Application $app
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function unblockuserAction(Application $app, $id)
    {

        // Check if user is blocked
        if($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $blocked = $this->isBlocked($app);
            if($blocked == 0){
                return $app->redirect($app['url_generator']->generate('homepage'));
            }
        }

        $userRepository = new UserRepository($app['db']);
        $data = [];
        $data['idForumUser'] = $id;
        $current = $this->getUserID($app);
        if ($data['idForumUser'] === $current){
            return $app->redirect($app['url_generator']->generate('homepage'));
        }
        $data['blocked'] = 1;
        $userRepository->update($data);
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

    /**
     * Register action.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function registerAction(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder(RegisterType::class, [], ['user_repository' => new UserRepository($app['db'])])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userRepository = new UserRepository($app['db']);
            $data['password'] = $app['security.encoder.bcrypt']->encodePassword($data['password'], '');

            $personal = [];
            $personal['name'] = $data['name'];
            unset($data['name']);
            $personal['surname'] = $data['surname'];
            unset($data['surname']);
            $personal['email'] = $data['email'];
            unset($data['email']);
            $personal['birthdate'] = $data['birthdate']->format("Y-m-d");
            unset($data['birthdate']);

            $roleRepository = new RoleRepository($app['db']);
            $data['idForumUserRole'] = $roleRepository->getUserID();
            $userRepository->add($data);
            $personal['idForumUser'] = $app['db']->lastInsertId();
            $userRepository->addData($personal);

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
            'auth/register.html.twig',
            [
                'form' => $form->createView(),
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