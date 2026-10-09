<?php

namespace Base\Consulting\Offering;

use Base\Consulting\Entity\Offering;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A rule an offering must keep before it is saved, that this bundle does
 * not know: a profession's (omnibase/legal's jurist profile refuses an
 * offering that announces what the law reserves to a regulated
 * profession), or the application's own. Each is an autoconfigured service;
 * the #[OfferingRules] constraint on Offering asks them all, and the
 * back office's form prints what they answer under the field they name.
 *
 *     final class NoPricesRule implements OfferingRuleInterface
 *     {
 *         public function check(Offering $offering): ?OfferingViolation
 *         {
 *             return null !== $offering->getPrice() ? new OfferingViolation('No price on this site.', 'price') : null;
 *         }
 *     }
 */
#[AutoconfigureTag(self::TAG)]
interface OfferingRuleInterface
{
    public const TAG = 'consulting.offering_rule';

    /** Why the offering may not be saved, or null when it may. */
    public function check(Offering $offering): ?OfferingViolation;
}
