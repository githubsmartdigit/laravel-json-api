<?php

/**
 * Author: Xooxx <xooxx.dev@gmail.com>
 * Date: 8/15/15
 * Time: 5:45 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Xooxx\Laravel\JsonApi;

use Xooxx\Laravel\JsonApi\Providers\Laravel4Provider;

abstract class ApiServiceProvider extends \Illuminate\Support\ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The transformer classes
     * @var array
     */
    protected $transformers = [];

    /**
     * Register the service provider.
     */
    public function register()
    {
        $provider = new Laravel4Provider();
        $this->app->singleton(JsonApiSerializer::class, $provider->provider($this->transformers));
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['jsonapi'];
    }
}