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
            throw LogicException::alreadyRegistered($registrable);
        }

        if (!\in_array($this->getTarget(), class_implements($registrable), true)) {
            throw InvalidArgumentException::subjectDoesNotImplementRequirement($registrable, $this->getTarget());
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
            throw LogicException::factoryTargetMustBeAnExistingInterface($this, $target);
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
            throw LogicException::noCandidatesFound($this);
        }

        return $result;
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
            throw LogicException::multipleCandidatesFound($this);
        }

        $registrable = reset($filteredRegistry);
        if (\in_array(\spl_object_hash($registrable), $this->unsharedRegistry, true)) {
            return clone $registrable;
        }

        return $registrable;
    }
}
