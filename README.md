# Dependency Injection Factory Component

## Why
To enable you to create service based factories rapidly, without having to configure
anything outside the factory, as the factory itself always knows what it will return, and thus 'requires' in order to do so. This becomes especially useful when combined with the Symfony autowiring (>= v3.3) functionality, as the only method you will need is the getTargetInterface() for everything to be done automatically.

## Installation
```
composer require aeviiq/factory
```
##### Symfony >= version 4
```php
// src/Kernel.php
namespace App;

use App\DependencyInjection\Compiler\CustomPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // ...

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FactoryCompilerPass());
    }
}
```
##### Symfony < version 4
```php
// src/AppBundle/AppBundle.php
namespace AppBundle;

use AppBundle\DependencyInjection\Compiler\CustomPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FactoryCompilerPass());
    }
}
```
## Declaration
```php
final class EncoderFactory extends AbstractFactory
{
    public function getEncoder(User $user): Encoder
    {
        // getOneBy ensures 1, and only 1 encoder is returned. 
        // In case multiple encoders (or none) are found, a LogicException will be thrown.
        // In case the result is optional, you could use the getOneOrNullBy().
        return $this->getOneBy(static function (Encoder $encoder) use ($user) {
            return $encoder->supports($user);
        });
    }

    protected function getTargetInterface(): string
    {
        // All services with this interface will automatically be wired to the factory 
        // without needing any additional service configuration.
        // Using autowire (Symfony >= v3.3) these few declaration lines are
        // all you would need to implement your factory.
        return Encoder::class;
    }
}
```

## Usage
```php
final class Foo
{
    /**
     * @var EncoderFactory
     */
    private $encoderFactory;

    public function __construct(EncoderFactory $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function doSomething(User $user): void
    {
        // ...
        $encoder = $this->encoderFactory->getEncoder($user);
        if (!$encoder->isValidPassword($user->getPassword, $presentedPassword, $user->getSalt())) {
            // ...
        }
        // ...
        
    }
}
```
