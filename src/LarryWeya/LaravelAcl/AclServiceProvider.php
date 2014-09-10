<?php namespace LarryWeya\LaravelAcl;

use Illuminate\Support\ServiceProvider;
use Zend\Permissions\Acl\Acl;

class AclServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('larry-weya/acl');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $acl = new Acl;
        $this->app->instance('acl', $acl);

        // listen to before event and set resource to null - which could be overridden by a Route binding
        $this->app->before(function($request)
        {
            // @todo: this is only called if LaravelAcl is used from within the start files
            $request->context = null;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('acl');
	}

}
