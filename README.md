# Consulting

An independent professional's services on their own
[omnibase](https://github.com/glitchr-studio/omnibase) site: what they offer
(`Offering`: a teachers' workshop, a keynote, a training, a consultation -
who it is for, how it runs, its price as it should be printed), the quote
request on omnibase's contact form and model (`QuoteRequest`, kept and
answered in the back office, its notice under the form), and the
`RegulatedActivityGuard`: on a site whose profession calls for it, no
offering of what only a regulated profession may do. Hours paid in advance
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
- [The regulated activity guard](docs/regulated-activity.md)
- [Hours paid in advance](docs/credits.md)

## Tests

`vendor/bin/phpunit` (or `php vendor/bin/phpunit -c vendor/omnibase/consulting/phpunit.xml.dist`
inside a host): the guard off, on, lifted by a status, and the credit bridge;
inside a host only, who writes in the back office
(`tests/Controller/Admin/OpenToAdminsTest`).

License: MIT since 2026-10-09; earlier versions remain published under LGPL-3.0-or-later.
