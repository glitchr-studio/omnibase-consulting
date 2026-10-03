<?php

namespace Base\Consulting\Form\Model;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Entity\QuoteRequest;
use Base\Form\Model\ContactModel;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * What the quote request form sends: omnibase's contact model (a name, an
 * e-mail, a phone, the message, the robots' trap) and what a quote needs -
 * the offering, the organisation, how many people, when, where, the budget,
 * and the notice read and accepted.
 */
class QuoteRequestModel extends ContactModel
{
    public ?Offering $offering = null;

    #[Assert\Length(max: 255)]
    public ?string $organisation = null;

    #[Assert\Length(max: 120)]
    public ?string $participants = null;

    #[Assert\Length(max: 255)]
    public ?string $dates = null;

    #[Assert\Length(max: 255)]
    public ?string $location = null;

    #[Assert\Length(max: 120)]
    public ?string $budget = null;

    #[Assert\IsTrue(message: 'quote.consent_required')]
    public bool $consent = false;

    public function toQuoteRequest(?string $locale = null): QuoteRequest
    {
        return (new QuoteRequest())
            ->setOffering($this->offering)
            ->setName($this->name)
            ->setEmail($this->email)
            ->setPhone($this->phone)
            ->setOrganisation($this->organisation)
            ->setParticipants($this->participants)
            ->setDates($this->dates)
            ->setLocation($this->location)
            ->setBudget($this->budget)
            ->setMessage($this->message)
            ->setLocale($locale);
    }
}
