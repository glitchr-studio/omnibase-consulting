# Offerings and quote requests

## Offering

`title`, `slug`, `activity` (`Enum\Activity`: training, talk, consulting,
information, writing, other), `summary`,
`description` (the back office's editor, or plain text), `audience`,
`format`, `duration`, `price` (as printed: "On quote"), `hours` (a package
sold by the hour, once the marketplace does), `languages`, `image`,
`quotable`, `position`, `visible`.

Twig: `consulting_offerings(n)` for a home page,
`consulting_can_pay(offering)`. `{% include '@Consulting/client/_offering.html.twig' %}`
renders a card. JSON-LD: a `Service` per offering, an `OfferCatalog` on
`/services`.

## The quote request

`Form\Type\QuoteRequestType` is omnibase's `Base\Form\Type\ContactType`
(name, e-mail, phone, message) with the offering, the organisation, the
participants, the dates, the place, the budget, and the consent to the
notice; its model `Form\Model\QuoteRequestModel` extends
`Base\Form\Model\ContactModel`.

It is guarded as glitchr/omnibase guards a form - the option `guard`
(`action: quote`; glitchr/omnibase's `docs/20-architecture/guard.md`): a
trap, the time it takes (a signed stamp), the lists of `base.guard.reputation`,
the captcha when the site has glitchr/omniguard. Without omniguard, the trap
and the time alone. A robot is refused on the form, before anything is kept.

```php
$form = $forms->createNamed('quote', QuoteRequestType::class, $model = new QuoteRequestModel());
if ($form->isSubmitted() && $form->isValid()) {
    $em->persist($model->toQuoteRequest($request->getLocale()));
}
```

The page keeps the request (`QuoteRequest`: new, answered, archived, the
back office's notes) and mails it to `consulting.recipient`, its reply-to
the visitor. The notice under the form says what is kept, why, for how long
(`retention_months`) and how to have it removed; `consulting:purge` keeps
that promise.

## Who writes in the back office

The two screens (`OfferingCrudController`, `QuoteRequestCrudController`) are
the site's administrator's (`ROLE_ADMIN`: the professional whose site it
is), not the super-admin's only: they carry omnibase/admin's
`#[OpenToAdmins]` - creating, editing and deleting a service; annotating,
deleting a quote request and its two buttons, `answered` and `archive`. The
attribute is omnibase/admin's from 7474f85.
