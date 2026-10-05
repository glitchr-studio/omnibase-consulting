<?php

namespace Base\Consulting\Validator;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Service\RegulatedActivityGuard;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RegulatedActivityValidator extends ConstraintValidator
{
    public function __construct(
        private readonly RegulatedActivityGuard $guard,
        private readonly ?TranslatorInterface $translator = null,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Offering || null === $reason = $this->guard->check($value)) {
            return;
        }
        $this->context->buildViolation($this->translator?->trans($reason, [], 'consulting') ?? $reason)
            ->atPath('activity')
            ->addViolation();
    }
}
