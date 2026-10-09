<?php

namespace Base\Consulting\Offering;

/** What a rule refuses in an offering: the words said, and the field they are said under. */
final readonly class OfferingViolation
{
    public function __construct(
        /** Already translated: the rule knows its own words. */
        public string $message,
        /** The offering's property the form prints it under: title, summary, description, activity... */
        public string $path = 'description',
    ) {
    }
}
