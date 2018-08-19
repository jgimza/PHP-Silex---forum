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

    public function registerAction(Application $app, Request $request)
    {
        $form = $app['form.factory']->createBuilder(RegisterType::class, [], ['user_repository' => new UserRepository($app['db'])])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userRepository = new UserRepository($app['db']);
            $data['password'] = $app['security.encoder.bcrypt']->encodePassword($data['password'], '');

            //TODO dodać więcej pól w formularzu, jak mail itp itd
            $roleRepository = new RoleRepository($app['db']);
            $data['idForumUserRole'] = $roleRepository->getUserID();

            $userRepository->add($data);

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
}