<?php namespace LarryWeya\LaravelAcl;

use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\Guard;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Auth\UserInterface as IlluminateUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Zend\Permissions\Acl\Acl;

class AclGuard extends Guard {

    /**
     * The LaravelAcl instance
     *
     * @var \Zend\Permissions\Acl\Acl
     */
    protected $user;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Auth\UserProviderInterface  $provider
     * @param  \Illuminate\Session\Store  $session
     * @param  \Zend\Permissions\Acl\Acl  $acl
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return void
     */
    public function __construct(UserProviderInterface $provider,
                                SessionStore $session,
                                Acl $acl,
                                Request $request = null)
    {
        $this->session = $session;
        $this->request = $request;
        $this->provider = $provider;
        $this->acl = $acl;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Auth\UserInterface|null
     */
    public function user()
    {
        if ($this->loggedOut) return;

        // If we have already retrieved the user for the current request we can just
        // return it back immediately. We do not want to pull the user data every
        // request into the method because that would tremendously slow an app.
        if ( ! is_null($this->user))
        {
            return $this->user;
        }

        $id = $this->session->get($this->getName());

        // First we will try to load the user using the identifier in the session if
        // one exists. Otherwise we will check for a "remember me" cookie in this
        // request, and if one exists, attempt to retrieve the user using that.
        $user = null;

        if ( ! is_null($id))
        {
            $user = $this->provider->retrieveByID($id);
        }

        // If the user is null, but we decrypt a "recaller" cookie we can attempt to
        // pull the user data on that cookie which serves as a remember cookie on
        // the application. Once we have a user we can return it to the caller.
        $recaller = $this->getRecaller();

        if (is_null($user) && ! is_null($recaller))
        {
            $user = $this->getUserByRecaller($recaller);
        }

        if(! is_null($user))
            $this->setUser($user);

        return $this->user;
    }

    /**
     * Set the current user of the application.
     * @todo: find a better way of catching whenever a user is resolved, from cookie, at login etc.
     *
     * @param  \Illuminate\Auth\UserInterface  $user
     * @return void
     */
    public function setUser(IlluminateUserInterface $user)
    {
        $this->user = $user;

        // if $user is valid let it add itself to the LaravelAcl however it sees fit
        if(! $this->acl->hasRole($this->user) )
            $user->attachToAcl($this->acl);

        $this->loggedOut = false;
    }

}
