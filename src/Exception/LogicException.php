<?php declare(strict_types = 1);

namespace Aeviiq\Factory\Exception;

final class LogicException extends \LogicException implements IException
{
    public static function alreadyRegistered(object $subject): LogicException
    {
        return new static(sprintf('%s is already registered.', get_class($subject)));
    }

    public static function multipleCandidatesFound(object $subject): LogicException
    {
        return new static(sprintf('Multiple services were found in "%s". The result is ambiguous.', get_class($subject)));
    }

    public static function noCandidatesFound(object $subject): LogicException
    {
        return new static(sprintf('Unable to find the requested service in "%s".', get_class($subject)));
    }

    public static function factoryTargetMustBeAnExistingInterface(object $subject, string $given): LogicException
    {
        return new static(sprintf('The target for "%s" must be an existing interface. "%s" given.', get_class($subject), $given));
    }

    public static function multipleFactoriesWithSameTarget(string $target, string $usedFactory, string $factory): LogicException
    {
        return new static(sprintf('"%s" is targeted by "%s" and "%s". This is not allowed.', $target, $usedFactory, $factory));
    }
}
