<?php

namespace Base\Consulting\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** A quote request: just arrived, answered, set aside. */
enum QuoteStatus: string implements TranslatableInterface
{
    case NEW = 'new';
    case ANSWERED = 'answered';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return 'quote.status.'.$this->value;
    }

    /** Its name in the `consulting` domain: what a select of the back office shows. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], 'consulting', $locale);
    }
}
