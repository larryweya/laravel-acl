laravel-acl
===========

ACL implementation using Zend's ACL as a route filter

Installation
------------

Add this repo to your ```repositories`` section within your ``composer.json```

    ```
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/larryweya/laravel-acl"
        }
    ],
    ```
    
Run ```composer update```

Configuration
-------------

Update your ```config/app.php``` as follows:

    Change your ```AuthManager``` to ```AclAuthManager``` that is provided by laravel-acl
    
    ```php
    'providers' => array(
		    ...
        'LarryWeya\LaravelAcl\AclAuthServiceProvider',
        ...
    ```
    
    Add the AclService provider to your providers array
    
    ```php
    'LarryWeya\LaravelAcl\AclServiceProvider'
    ```
    
    Add the Acl facade to your aliases
    
    ```php
    'Acl'      => 'LarryWeya\LaravelAcl\Facades\Acl'
    ```
    
    

