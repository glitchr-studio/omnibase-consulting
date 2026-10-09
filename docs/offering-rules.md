# Rules an offering keeps

The bundle refuses nothing by itself: what an offering may say is the site's
business, or its profession's. A rule is a service implementing
`Base\Consulting\Offering\OfferingRuleInterface` (autoconfigured, tag
`consulting.offering_rule`); the `#[OfferingRules]` constraint on `Offering`
asks every rule before an offering is saved, and the back office's form
prints the reason under the field the rule names.

```php
final class NoPricesRule implements OfferingRuleInterface
{
    public function check(Offering $offering): ?OfferingViolation
    {
        return null !== $offering->getPrice() ? new OfferingViolation('No price on this site.', 'price') : null;
    }
}
```

## A jurist's site

The guard on what French law no. 71-1130 of 31 December 1971 reserves to the
regulated professions (personalised legal advice, deeds drafted for others)
was this bundle's `RegulatedActivityGuard` until 2026-10-09. It is now the
jurist's profile of [omnibase/legal](https://github.com/glitchr-studio/omnibase-legal):

```yaml
# config/packages/legal.yaml
legal:
    profession: jurist
    jurist:
        authorized_status: ~     # teacher (art. 57), regulated_profession (art. 59)
```

With omnibase/consulting installed, its rule refuses an offering whose title,
summary or presentation announces a legal consultation or the drafting of
deeds. `consulting.regulated_activity_guard` and `consulting.authorized_status`
are read for one version: off, they change nothing (and say they are
deprecated); on, they say where the guard went. The activities
`legal_consultation` and `deed_drafting` are gone from `Enum\Activity`: a
stored one reads as `other`.
