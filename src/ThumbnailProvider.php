<?php
namespace Bolt\Thumbs;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class ThumbnailProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    protected $routePatternBase = '/thumbs';

    public function register(Application $app)
    {
        $app['thumbnails.paths'] = array(
            'files' => $app['resources']->getPath('files'),
            'theme' => $app['resources']->getPath('themebase')
        );

        $app['thumbnails'] = $app->share(
            function ($app) {
                return new ThumbnailResponder($app, $app['request']);
            }
        );

        $app['thumbnails.response'] = $app->share(
            function () {
                return new Response();
            }
        );
    }

    public function connect(Application $app)
    {
        /** @var \Silex\ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->get(
            '/{thumb}',
            function (Application $app) {
                $response = $app['thumbnails']->respond();
                if ($response) {
                    return $response;
                }

                $app->pass();
            }
        )->assert('thumb', '.+')
        ->bind('thumbnails');

        return $controllers;
    }

    public function boot(Application $app)
    {
        if (isset($app['thumbnails.route_pattern'])) {
            $this->routePatternBase = $app['thumbnails.route_pattern'];
        }
    }
}
