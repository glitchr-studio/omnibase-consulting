# Consulting

An independent professional's services on their own
[omnibase](https://github.com/glitchr-studio/omnibase) site: what they offer
(`Offering`: a teachers' workshop, a keynote, a training, a consultation -
who it is for, how it runs, its price as it should be printed), the quote
request on omnibase's contact form and model (`QuoteRequest`, kept and
answered in the back office, its notice under the form), and the rules an
offering keeps that the site or its profession adds (`OfferingRuleInterface`;
a jurist's guard on what the law reserves to lawyers and notaries is
[omnibase/legal](https://github.com/glitchr-studio/omnibase-legal)'s). Hours paid in advance
will be [omnibase/marketplace](https://github.com/glitchr-studio/omnibase-marketplace)
`Credit`s: the hook is there (`Service\CreditBridge`), nothing more.

```bash
composer require omnibase/consulting:dev-main
```

| | |
|---|---|
| `/services` | the offerings (`consulting_offerings`) |
| `/services/{slug}` | one offering, its facts, "Ask for a quote" (`consulting_offering`) |
| `/quote` (`?offering=<slug>`) | the quote request (`consulting_quote`) |
| widget `consulting_quotes` | the requests waiting for an answer |
| `consulting:purge` | the requests older than the notice says, deleted (cron, daily) |

## Documentation

- [Installation and configuration](docs/installation.md)
- [Offerings and quote requests](docs/offerings-and-quotes.md)
- [Rules an offering keeps](docs/offering-rules.md)
- [Hours paid in advance](docs/credits.md)

## Tests

`vendor/bin/phpunit` (or `php vendor/bin/phpunit -c vendor/omnibase/consulting/phpunit.xml.dist`
inside a host): the offering rules, the activities' enum, the credit bridge;
inside a host only, who writes in the back office
(`tests/Controller/Admin/OpenToAdminsTest`).

License: MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
