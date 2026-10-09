<?php

namespace Base\Consulting\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * What an offering is: a training, a talk or a show, consulting,
 * documentary information, writing content. Nothing here is reserved to a
 * regulated profession: what the law of 31 December 1971 keeps for lawyers
 * and notaries (a personalised legal consultation, deeds drafted for
 * others) is not an offering of this bundle - a jurist's site guards
 * against it with omnibase/legal (legal.profession: jurist).
 */
enum Activity: string implements TranslatableInterface
{
    case TRAINING = 'training';
    case TALK = 'talk';
    case CONSULTING = 'consulting';
    case INFORMATION = 'information';
    case WRITING = 'writing';
    case OTHER = 'other';

    public function label(): string
    {
        return 'activity.'.$this->value;
    }

    /** Its name in the `consulting` domain: what a select of the back office shows. */
    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], 'consulting', $locale);
    }
}
