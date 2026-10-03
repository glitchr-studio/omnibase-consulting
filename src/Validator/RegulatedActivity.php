<?php

namespace Base\Consulting\Validator;

use Symfony\Component\Validator\Constraint;

/** The offering stays clear of what only a regulated profession may do (Service\RegulatedActivityGuard). */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class RegulatedActivity extends Constraint
{
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
