# Dice (Dependency Injection Container) Wrapper for WordPress

[![License](http://poser.pugx.org/pangolia/container/license)](https://packagist.org/packages/pangolia/container)

A small wrapper for Dice (https://github.com/Level-2/Dice) to auto-register and auto-instantiate objects. Primarily created for WordPress development; can be used for plugins & themes.

## Installation
Use composer to install the package.
````bash
composer require pangolia/container
````

## Usage
(See container example in ``src/ExampleContainer.php``)

Let's say you have a project folder that contains the following classes in the following PSR-4 compliant directory structure  with the namespace ``ProjectNamespace``:
- ``src/Core/Config.php``
- ``src/Core/Setup.php``
- ``src/Cli/ProjectCli.php``
- ``src/Cli/AnotherCli.php``
- ``src/Module/ModuleA/Module.php``
- ``src/Module/ModuleB/Module.php ``
- ``src/Module/ModuleB/Folder/Helper.php``

And that each has the following class structure:

````php
namespace ProjectNamespace\Core;

use Pangolia\Container\Registrable;

class Setup implements Registrable {

    public function __construct() {
     // Register your dependencies & class properties here
    }
    
    public function register(){
     // Register your class logic here
    }
}
````

In the services() method of the container (see below), you can set the namespace hierarchies, and it will: 
- Auto-find and auto-instantiate all the classes within these folders
- Call the register() method if the class implements the Registrable interface which holds the WordPress actions and filters - effectively replacing the need to manually add them in one place
- Auto-create a "shared" dice rule, so the class will be instantiated only once, even if it's being injected multiple times. 

You can use the rules() method to set your own Dice rules:

````php
namespace ProjectNamespace\Container;

use Pangolia\Container\Container as BuildContainer;

class Container extends BuildContainer {
    
    /**
     * Container services.
     *
     * @inheritDoc
     */
    protected function services(): array {
      return [
       'ProjectNamespace\Core',
       'ProjectNamespace\Cli',
       'ProjectNamespace\Modules',
      ];
    }
    
    /**
     * Container rules.
     *
     * @inheritDoc
     */
    protected function rules(): array {
        return [
          \ProjectNamespace\Core\Config::class => [
            'constructParams' => [ 'config' => [ 'key' => 'value'] ],
        ],
      ];
    }
}
````
Combined with Dice's autowiring functionalities + Composer's Autoloader:
- All files will be automatically included 
- All classes inside the namespace hierarchies will be automatically instantiated and registered when creating and adding new classes & files
- All dependencies will be automatically injected 

When instantiating the container you have to pass 1 argument:
- ``$psr4_prefixes``(array): Composer's PSR4 prefixes which you can get with the getPrefixesPsr4() method from Composer (see example below). This will make sure it will target the project's full folder path to find all the project files.

Then you can call these methods to configure the container 
- ``set_namespace``(string): Your project namespace
- ``set_environment``(string): ("dev" or "prod") To set the project's environment (optional: default set to "dev")
- ``set_cache_folder``(string): To set the project's cache folder (optional: default set to "build")
````php
$composer = ( require 'vendor/autoload.php' )->getPrefixesPsr4();

( $container = new \ProjectNamespace\Container( $composer ) )
 ->set_namespace( 'ProjectNamespace' )
 ->set_environment( 'dev' )
 ->register();


// Example of getting an object instance
if ( $container->has( 'class_name' ) ) {
  $service = $container->get( 'class_name' );
}
````

Let's say you have a folder structure like this:
- ``src`` (which includes your project's source files)
- ``build``(which includes your project's generated build files)

When the container's environment is set to "prod" it will cache the results and create a file in the "build" folder (as defined in the ``$config`` array)
- ``build/container/services.php`` - returns an array list of all the classes we want to instantiate based on the services() method

The container will then use this list to instantiate and register the classes so there are no performance drawbacks on production. 

When adding new classes & files then you have to set the environment back to "dev" so it will delete all the cached files on the initial load (or your can just delete the cached files yourself manually)