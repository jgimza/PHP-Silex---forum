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
     * {@inheritdoc}
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
        return $controller;
    }
    /**
     * Login action.
     *
     * @param \Silex\Application                        $app     Silex application
     * @param \Symfony\Component\HttpFoundation\Request $request HTTP Request
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
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
     * @param \Silex\Application $app Silex application
     *
     * @return \Symfony\Component\HttpFoundation\Response HTTP Response
     */

    public function logoutAction(Application $app)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.html.twig', []);
    }
    /**
     *
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
     *
     *
     * @param Application $app
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */

    public function makeadminAction(Application $app, $id)
    {
        $roleRepository = new RoleRepository($app['db']);
        $data = [];
        $data['idForumUser'] = $id;
        $data['idForumUserRole'] = $roleRepository->getAdminID();
        $userRepository = new UserRepository($app['db']);
        $userRepository->update($data);
        return $app->redirect($app['url_generator']->generate('homepage'));
    }

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

    private function getUserID(Application $app)
    {
        $login = $app['security.token_storage']->getToken()->getUser()->getUsername();
        $userRepository = new UserRepository($app['db']);
        return $userRepository->getUserByLogin($login)['idForumUser'];
    }
}