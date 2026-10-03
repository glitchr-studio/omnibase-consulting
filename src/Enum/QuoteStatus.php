<?php

namespace Base\Consulting\Enum;

/** A quote request: just arrived, answered, set aside. */
enum QuoteStatus: string
{
    case NEW = 'new';
    case ANSWERED = 'answered';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return 'quote.status.'.$this->value;
    }
}
