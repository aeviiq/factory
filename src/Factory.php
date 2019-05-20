<?php declare(strict_types = 1);

namespace Aeviiq\Factory;

interface Factory
{
    public function register(object $service, bool $shared): void;

    /**
     * @return string FQN of the target class
     */
    public function getTarget(): string;

    public function getByFqn(string $fqn): object;
}
