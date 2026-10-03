# The regulated activity guard

French law no. 71-1130 of 31 December 1971 (art. 54 and following) reserves
personalised legal advice and the drafting of deeds for others to the
professions it allows: a lawyer, a notary, a university teacher of law
(art. 57), another regulated profession within its own field (art. 59).
Training, documentary information (art. 66-1), writing content, talks are
open to anyone.

```yaml
consulting:
    regulated_activity_guard: true     # a jurist's site
    authorized_status: ~               # or lawyer, notary, teacher, regulated_profession
```

On, without an authorized status, `Service\RegulatedActivityGuard`:

- hides the reserved activities (`legal_consultation`, `deed_drafting`) from
  the back office's choice;
- refuses an offering (the `#[RegulatedActivity]` constraint on `Offering`)
  filed under one of them, or whose words announce one - "consultation
  juridique personnalisée", "conseil juridique sur mesure", "rédaction
  d'actes", "legal advice"... - whatever activity it was filed under.

```php
$guard->check($offering);   // null, or 'guard.legal_consultation' / 'guard.deed_drafting' / 'guard.wording'
```

Off by default: a mathematics consultant has nothing to fear from it. It is a
safeguard written into the code, not legal advice: the profession's own
rules and its order's review still apply.
