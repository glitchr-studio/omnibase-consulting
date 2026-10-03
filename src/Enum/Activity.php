<?php

namespace Base\Consulting\Enum;

/**
 * What an offering is, as the law sees it. Training, documentary
 * information (art. 66-1 of the law of 31 December 1971), writing content,
 * a talk or a show are open to anyone; a personalised legal consultation
 * and the drafting of deeds for others are not (art. 54): the
 * RegulatedActivityGuard keeps them for a status that allows them.
 */
enum Activity: string
{
    case TRAINING = 'training';
    case TALK = 'talk';
    case CONSULTING = 'consulting';
    case INFORMATION = 'information';
    case WRITING = 'writing';
    case LEGAL_CONSULTATION = 'legal_consultation';
    case DEED_DRAFTING = 'deed_drafting';
    case OTHER = 'other';

    public function label(): string
    {
        return 'activity.'.$this->value;
    }

    /** Reserved by the law to a regulated profession. */
    public function isRegulated(): bool
    {
        return \in_array($this, [self::LEGAL_CONSULTATION, self::DEED_DRAFTING], true);
    }
}
