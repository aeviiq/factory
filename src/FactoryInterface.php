<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Aeviiq\Factory\Exception\LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @template T of object
 */
interface FactoryInterface extends ContainerAwareInterface
{
    public function register(string $serviceId): void;

    /**
     * @phpstan-return class-string<T>
     *
     * @return string FQN of the target class
     */
    public function getTarget(): string;

    /**
     * @phpstan-param class-string<T> $fqn
     *
     * @phpstan-return T
     *
     * @throws LogicException
     */
    public function getByFqn(string $fqn): object;
}
