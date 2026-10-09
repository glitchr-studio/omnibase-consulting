<?php

namespace Base\Consulting\Validator;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Offering\OfferingRuleInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class OfferingRulesValidator extends ConstraintValidator
{
    /** @param iterable<OfferingRuleInterface> $rules */
    public function __construct(#[AutowireIterator(OfferingRuleInterface::TAG)] private readonly iterable $rules = [])
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Offering) {
            return;
        }
        foreach ($this->rules as $rule) {
            if (null !== $violation = $rule->check($value)) {
                $this->context->buildViolation($violation->message)->atPath($violation->path)->addViolation();
            }
        }
    }
}
