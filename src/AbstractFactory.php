<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Aeviiq\Factory\Exception\InvalidArgumentException;
use Aeviiq\Factory\Exception\LogicException;

abstract class AbstractFactory implements Factory
{
    /**
     * @var object[]
     */
    private $registry = [];

    /**
     * @var string[]
     */
    private $unsharedRegistry = [];

    final public function register(object $registrable, bool $shared): void
    {
        if (\in_array($registrable, $this->registry, true)) {
            throw $this->createLogicException(\sprintf('%s is already registered.', \get_class($registrable)));
        }

        if (!\in_array($this->getTarget(), \class_implements($registrable), true)) {
            throw $this->createInvalidArgumentException(\sprintf('%s must implement %s', \get_class($registrable), $this->getTarget()));
        }

        if (!$shared) {
            $this->unsharedRegistry[] = spl_object_hash($registrable);
        }

        $this->registry[] = $registrable;
    }

    final public function getTarget(): string
    {
        $target = $this->getTargetInterface();
        if (!\interface_exists($target)) {
            throw $this->createLogicException(\sprintf('The target for "%s" must be an existing interface. "%s" given.', \get_class($this), $target));
        }

        return $target;
    }

    public function getByFqn(string $fqn): object
    {
        return $this->getOneBy(static function (object $service) use ($fqn): bool {
            return $fqn === \get_class($service);
        });
    }

    abstract protected function getTargetInterface(): string;

    /**
     * @throws LogicException When multiple candidates were found.
     */
    protected function getOneOrNullBy(callable $criteria): ?object
    {
        return $this->search($criteria);
    }

    /**
     * @throws LogicException When no viable candidates were found.
     */
    protected function getOneBy(callable $criteria): object
    {
        $result = $this->search($criteria);
        if (null === $result) {
            throw $this->createLogicException(\sprintf('Unable to find the requested service in "%s".', \get_class($this)));
        }

        return $result;
    }

    protected function createLogicException(string $message): \LogicException
    {
        return new LogicException($message);
    }

    protected function createInvalidArgumentException(string $message): \InvalidArgumentException
    {
        return new InvalidArgumentException($message);
    }

    /**
     * @throws LogicException When multiple candidates were found.
     */
    private function search(callable $criteria): ?object
    {
        $filteredRegistry = \array_filter($this->registry, $criteria);
        if (empty($filteredRegistry)) {
            return null;
        }

        if (\count($filteredRegistry) > 1) {
            throw $this->createLogicException(\sprintf('Multiple services were found in "%s". The result is ambiguous.', \get_class($this)));
        }

        $registrable = \reset($filteredRegistry);
        if (\in_array(\spl_object_hash($registrable), $this->unsharedRegistry, true)) {
            return clone $registrable;
        }

        return $registrable;
    }
}
