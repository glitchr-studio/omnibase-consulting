<?php

namespace Base\Consulting\Validator;

use Symfony\Component\Validator\Constraint;

/** The offering keeps every rule the site has (Base\Consulting\Offering\OfferingRuleInterface). */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class OfferingRules extends Constraint
{
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
