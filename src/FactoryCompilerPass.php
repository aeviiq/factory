<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class FactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definitions = $container->getDefinitions();
        $factories = [];
        foreach ($definitions as $serviceId => $definition) {
            if ($definition->isAbstract()) {
                unset($definitions[$serviceId]);
                continue;
            }

            if (null === $r = $container->getReflectionClass($definition->getClass(), false)) {
                unset($definitions[$serviceId]);
                continue;
            }

            if (!$r->implementsInterface(FactoryInterface::class)) {
                continue;
            }

            $definition->addMethodCall('setContainer', [new Reference('service_container')]);
            $factories[] = [$r->newInstanceWithoutConstructor()->getTarget(), $definition];
            unset($definitions[$serviceId]);
        }

        foreach ($definitions as $serviceId => $definition) {
            foreach ($factories as [$target, $factory]) {
                if (null === $r = $container->getReflectionClass($definition->getClass())) {
                    continue 2;
                }

                if (!$r->implementsInterface($target)) {
                    continue;
                }

                $definition->setPublic(true);
                $factory->addMethodCall('register', [$serviceId]);
            }
        }
    }
}
