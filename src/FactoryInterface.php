<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Aeviiq\Factory\Exception\LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @psalm-template T of object
 * @phpstan-template T of object
 */
interface FactoryInterface extends ContainerAwareInterface
{
    public function register(string $serviceId): void;

    /**
     * @psalm-return class-string<T>
     * @phpstan-return class-string<T>
     * @return string FQN of the target class
     */
    public function getTarget(): string;

    /**
     * @psalm-param class-string<T> $fqn
     * @phpstan-param class-string<T> $fqn
     *
     * @psalm-return T
     * @phpstan-return T
     *
     * @throws LogicException
     */
    public function getByFqn(string $fqn): object;
}
