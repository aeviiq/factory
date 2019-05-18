<?php declare(strict_types = 1);

namespace Aeviiq\Factory\Exception;

final class InvalidArgumentException extends \InvalidArgumentException implements IException
{
    public static function subjectDoesNotImplementRequirement(object $subject, string $requirement): InvalidArgumentException
    {
        return new self(sprintf('%s must implement %s', get_class($subject), $requirement));
    }
}
