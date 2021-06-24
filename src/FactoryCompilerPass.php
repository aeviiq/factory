<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class FactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $factories = [];
        $targets = [];
        foreach ($container->getDefinitions() as $serviceId => $definition) {
            if ($definition->isAbstract() || null === $r = $container->getReflectionClass($definition->getClass(), false)) {
                continue;
            }

            if (!$r->implementsInterface(FactoryInterface::class)) {
                foreach ($r->getInterfaces() as $interface => $reflectionClass) {
                    $targets[$interface][] = $serviceId;
                }

                continue;
            }

            /** @var FactoryInterface<object> $factory */
            $factory = $r->newInstanceWithoutConstructor();
            $definition->addMethodCall('setContainer', [new Reference('service_container')]);
            $factories[] = [$factory->getTarget(), $definition];
        }

        foreach ($factories as [$target, $factory]) {
            foreach ($targets[$target] as $serviceId) {
                $container->getDefinition($serviceId)->setPublic(true);
                $factory->addMethodCall('register', [$serviceId]);
            }
        }
    }
}
