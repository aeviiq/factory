<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

interface Factory extends ContainerAwareInterface
{
    public function register(string $serviceId): void;

    /**
     * @return string FQN of the target class
     */
    public function getTarget(): string;

    public function getByFqn(string $fqn): object;
}
