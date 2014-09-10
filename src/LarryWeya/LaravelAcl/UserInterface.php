<?php namespace LarryWeya\LaravelAcl;

use Illuminate\Auth\UserInterface as IlluminateUserInterface;
use Zend\Permissions\Acl\Acl;

interface UserInterface extends IlluminateUserInterface {
    public function attachToAcl(Acl $acl);
}