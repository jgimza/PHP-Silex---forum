<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Controller\FaqController;
use Controller\CommunityController;
use Controller\SectionController;
use Controller\TopicController;
use Repository\SectionRepository;
use Repository\SubforumRepository;
use Controller\AuthController;


//Request::setTrustedProxies(array('127.0.0.1'));
$app->get('/', function () use ($app) {
    $sectionRepository = new SectionRepository($app['db']);
    $subforumRepository = new SubforumRepository($app['db']);


    return $app['twig']->render(
        'index.html.twig',
        [
            'subforum' => $subforumRepository->findAll($app),
            'section' => $sectionRepository->findAll($app),
            'topics' => $sectionRepository->findTopicData($app)
        ]
    );
})
    ->bind('homepage')
;

$app->mount('/faq', new FaqController());
$app->mount('/community', new CommunityController());
$app->mount('/', new SectionController());
$app->mount('/auth', new AuthController());
$app->mount('/{slug}/topic/', new TopicController());

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
