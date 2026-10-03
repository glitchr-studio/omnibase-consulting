<?php

namespace Base\Consulting\Service;

use Base\Consulting\Entity\Offering;

/**
 * Where hours paid in advance will plug in: omnibase/marketplace's
 * generic `Credit` (plan 0 §5, written with the events platform) sells an
 * offering's package of hours and counts them down. Until that class
 * exists, nothing is sold - the offering shows its price and the quote
 * request. Nothing else is written here on purpose: no entity of hours of
 * our own, no copy of forge's HourCredit.
 */
final class CreditBridge
{
    public const CREDIT_CLASS = 'Base\\Marketplace\\Entity\\Credit';

    /** Whether the marketplace can sell hours at all. */
    public function isAvailable(): bool
    {
        return class_exists(self::CREDIT_CLASS);
    }

    /** Whether this offering could be paid by the hour now. */
    public function canSell(Offering $offering): bool
    {
        return $this->isAvailable() && null !== $offering->getHours();
    }
}
