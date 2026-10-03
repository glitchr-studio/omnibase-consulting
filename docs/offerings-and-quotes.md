# Offerings and quote requests

## Offering

`title`, `slug`, `activity` (`Enum\Activity`: training, talk, consulting,
information, writing, legal_consultation, deed_drafting, other), `summary`,
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
(name, e-mail, phone, message, the off-screen `website` trap) with the
offering, the organisation, the participants, the dates, the place, the
budget, and the consent to the notice; its model
`Form\Model\QuoteRequestModel` extends `Base\Form\Model\ContactModel`.

```php
$form = $forms->createNamed('quote', QuoteRequestType::class, $model = new QuoteRequestModel());
if ($form->isSubmitted() && $form->isValid() && !$model->isRobot()) {
    $em->persist($model->toQuoteRequest($request->getLocale()));
}
```

The page keeps the request (`QuoteRequest`: new, answered, archived, the
back office's notes) and mails it to `consulting.recipient`, its reply-to
the visitor. A robot that fills the trap is thanked; nothing is kept nor
sent. The notice under the form says what is kept, why, for how long
(`retention_months`) and how to have it removed; `consulting:purge` keeps
that promise.
