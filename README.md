laravel-acl
===========

ACL implementation using Zend's ACL as a route filter

Installation
------------

Add this repo to your `repositories` section within your `composer.json`

    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/larryweya/laravel-acl"
        }
    ],
    
Run `composer update`

Configuration
-------------

#### Update your `config/app.php` as follows:

Replace your `AuthManager` with `AclAuthManager` that is provided by `laravel-acl`

    'providers' => array(
        ...
        'LarryWeya\LaravelAcl\AclAuthServiceProvider',
        ...
    );

Add the Acl service provider to your providers array
    
    'LarryWeya\LaravelAcl\AclServiceProvider'
    
Add the Acl facade to your aliases

    'Acl'      => 'LarryWeya\LaravelAcl\Facades\Acl'
    
#### Define your Acl rules

Create and `app/acl.php` file and include it from within your `start/global.php`

```php
require app_path().'/filters.php';

/*
|--------------------------------------------------------------------------
| Require The Acl File
|--------------------------------------------------------------------------
|
*/

require app_path().'/acl.php';

```

Sample `app/acl.php`

```php
/**
 * Define some roles/permissions
 */
Acl::addRole('articles.create');
Acl::addRole('articles.publish');
Acl::addRole('articles.archive');

/**
 * Define rules that spcify who has access to what
 */
Ac::allow('articles.create', null /* any resource */, 'create_articles');
Ac::allow('articles.publish', null /* any resource */, 'publish_articles');
Ac::allow('articles.archive', null /* any resource */, 'archive_articles');
```
    
#### Implement `LaravelAcl\UserInterface` within your `User` class

LaravelAcl includes a custom `UserInterface` that derives from Laravel's UserInterface class which requires you to implement the `attachToAcl` function. This function is called whenever the `User` of the request is resolved. You should primarily use it to attach the user to the Acl

```php
public function attachToAcl(Acl $acl)
{
    $acl->addRole($acl);
}
```

You should however use it to attach other permissions that this user has/inherits from, perhaps based on soms DB persisted data.

    NOTE: any additional roles you define here must have already been added to the Acl, see the Define your Acl rules above
    
```php
public function attachToAcl(Acl $acl)
{
    $roles = $this->getRoles(); /// array('articles.create', 'articles.publish');
    $acl->addRole($acl, $roles);
}
```
    
#### Add a `filter` to check these permissions

```php
Route::filter('acl', function($route, $request, $permission = null)
{
    if(!Acl::isAllowed(Auth::user(), $request->context, $permission))
        return Response::make('Forbidden', 403);
});
```
 
You can now attach the acl filter to your routes, passing the required permission as a second argument

```php
Route::get('articles/create', array('before' => 'auth|acl:create_articles'));
```

#### Adavanced usage - Object level permissions

To handle access control at the object level, define a rule that accepts an instance of an `Zend\Permissions\Acl\Assertion\AssertionInterface` implementation, that will be used to determine whether the user has access e.g.

```php
class IsOwnerAssertion implements Zend\Permissions\Acl\Assertion\AssertionInterface {
    public function assert(Zend\Permissions\Acl\Acl $acl,
                           Zend\Permissions\Acl\Role\RoleInterface $role = null,
                           Zend\Permissions\Acl\Resource\ResourceInterface $resource = null,
                           $privilege = null)
    {
        return $role->id == $resource->author_id;
    }
}

Acl::allow(null, null, 'edit_article', new RecordSetAddRecordsAssertion());
```

The resource in the above example has to be set on the request object by you, e.g.

In `app/routes.php`

```php
Route::bind('article', function($value, $route) {
    try
    {
        $article = Article::where('id', $value)->firstOrFail();
        Acl::addResource($article); // add it as a resource - Note, Article has to implement Zend\Permissions\Acl\Resource\ResourceInterface;
        Route::getCurrentRequest()->context = $article; // set the article as the request's context
        return $article;
    }
    catch(ModelNotFoundException $e)
    {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    }
});

Route::get('articles/{article}/edit', array('before' => 'auth|acl:edit_article'));
```
