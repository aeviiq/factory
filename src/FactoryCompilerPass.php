<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Aeviiq\Factory\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definitions = $container->getDefinitions();
        $factories = [];
        foreach ($definitions as $k => $d) {
            if ($d->isAbstract()) {
                unset($definitions[$k]);
                continue;
            }

            if (null === $r = $container->getReflectionClass($d->getClass(), false)) {
                unset($definitions[$k]);
                continue;
            }

            if ($r->implementsInterface(Factory::class)) {
                $t = $r->newInstanceWithoutConstructor()->getTarget();
                if (isset($factories[$t])) {
                    throw LogicException::multipleFactoriesWithSameTarget($t, $factories[$t]->getClass(), $r->getName());
                }
                $factories[$t] = $d;
                unset($definitions[$k]);
            }
        }

        foreach ($definitions as $d) {
            foreach ($factories as $target => $factory) {
                if (null === $r = $container->getReflectionClass($d->getClass())) {
                    continue 2;
                }

                if ($r->implementsInterface($target)) {
                    $factory->addMethodCall('register', [$d, $d->isShared()]);
                    continue 2;
                }
            }
        }
    }
}
