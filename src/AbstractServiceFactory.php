<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Aeviiq\Factory\Exception\LogicException;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractServiceFactory implements FactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[][]
     */
    private $serviceIds = [];

    final public function register(string $serviceId): void
    {
        $this->serviceIds[$this->getTarget()][] = $serviceId;
    }

    final public function getTarget(): string
    {
        $target = $this->getTargetInterface();
        if (!\interface_exists($target)) {
            throw $this->createLogicException(\sprintf('The target for "%s" must be an existing interface. "%s" given.', \get_class($this), $target));
        }

        return $target;
    }

    final public function getByFqn(string $fqn): object
    {
        return $this->getOneBy(static function (object $service) use ($fqn): bool {
            return \get_class($service) === $fqn;
        });
    }

    final public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    abstract protected function getTargetInterface(): string;

    /**
     * @return object[] The services that are registered with this factory.
     */
    protected function getServices(): array
    {
        return \array_map(function (string $serviceId) {
            return $this->container->get($serviceId);
        }, $this->serviceIds[$this->getTarget()] ?? []);
    }

    /**
     * @throws LogicException When no service was found.
     */
    protected function getOneBy(callable $criteria): object
    {
        $result = $this->getOneOrNullBy($criteria);
        if (null === $result) {
            throw $this->createLogicException(\sprintf('Unable to find the requested service in "%s".', \get_class($this)));
        }

        return $result;
    }

    /**
     * @throws LogicException When multiple services were found.
     */
    protected function getOneOrNullBy(callable $criteria): ?object
    {
        $services = \array_filter($this->getServices(), $criteria);
        if (empty($services)) {
            return null;
        }

        if (\count($services) > 1) {
            throw $this->createLogicException(\sprintf('Multiple services were found in "%s". The result is ambiguous.', \get_class($this)));
        }

        return \array_shift($services);
    }

    protected function createLogicException(string $message): \LogicException
    {
        return new LogicException($message);
    }
}
