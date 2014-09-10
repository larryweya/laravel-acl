<?php namespace LarryWeya\LaravelAcl;

use Illuminate\Auth\AuthManager;

/**
 * Class AclAuthManager Creates a custom Guard subclass that overrides the user() function to setup the LaravelAcl
 * @package LarryWeya\LaravelAcl
 */
class AclAuthManager extends AuthManager {

    /**
     * Call a custom driver creator.
     *
     * @param  string  $driver
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        $custom = parent::callCustomCreator($driver);

        if ($custom instanceof AclGuard) return $custom;

        return new AclGuard($custom, $this->app['session.store'], $this->app['acl']);
    }

    /**
     * Create an instance of the database driver.
     *
     * @return \Illuminate\Auth\Guard
     */
    public function createDatabaseDriver()
    {
        $provider = $this->createDatabaseProvider();

        return new AclGuard($provider, $this->app['session.store'], $this->app['acl']);
    }

    /**
     * Create an instance of the Eloquent driver.
     *
     * @return \Illuminate\Auth\Guard
     */
    public function createEloquentDriver()
    {
        $provider = $this->createEloquentProvider();

        return new AclGuard($provider, $this->app['session.store'], $this->app['acl']);
    }
}
