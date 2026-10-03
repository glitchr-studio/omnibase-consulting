<?php

namespace Base\Consulting\Service;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * What a site may offer when its profession is not the one the law
 * reserves legal advice to (French law no. 71-1130 of 31 December 1971,
 * art. 54 and following): no personalised legal consultation, no deed
 * drafted for someone else - unless the site says it holds a status that
 * allows it (a lawyer, a notary, a university teacher under art. 57,
 * another regulated profession within its own field under art. 59).
 * Training, documentary information (art. 66-1), writing content, talks
 * are open to all.
 *
 * Off by default (consulting.regulated_activity_guard): a mathematics
 * consultant has nothing to fear from it; a jurist's site turns it on.
 * The guard reads the activity chosen, and the words of the offering - so
 * an offering filed as "training" but sold as "consultation juridique
 * personnalisée" is caught too. It is a safeguard, not legal advice: the
 * profession's own rules still apply.
 */
final class RegulatedActivityGuard
{
    /** The words of a reserved activity, in French and in English. */
    private const PATTERNS = [
        '/\bconsultations?\s+juridiques?\b/iu',
        '/\bconseils?\s+juridiques?\s+(personnalis|individuel|sur\s+mesure)/iu',
        '/\br[ée]daction\s+d[\'’]actes?\b/iu',
        '/\bavis\s+juridiques?\s+(personnalis|individuel)/iu',
        '/\blegal\s+(advice|consultation|opinion)s?\b/iu',
        '/\bdraft(ing)?\s+(of\s+)?(contracts?|deeds?|legal\s+documents?)\s+for\s+(you|clients?|others)\b/iu',
    ];

    public function __construct(
        #[Autowire('%consulting.regulated_activity_guard%')] private readonly bool $enabled = false,
        #[Autowire('%consulting.authorized_status%')] private readonly ?string $authorizedStatus = null,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /** Whether the site holds a status that allows legal advice. */
    public function isAuthorized(): bool
    {
        return null !== $this->authorizedStatus && '' !== $this->authorizedStatus;
    }

    /**
     * Why the offering may not be published, or null when it may.
     *
     * @return string|null a translation key of the "consulting" domain
     */
    public function check(Offering $offering): ?string
    {
        if (!$this->enabled || $this->isAuthorized()) {
            return null;
        }
        if ($offering->getActivity()->isRegulated()) {
            return Activity::DEED_DRAFTING === $offering->getActivity() ? 'guard.deed_drafting' : 'guard.legal_consultation';
        }
        $text = $offering->getText();
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $text)) {
                return 'guard.wording';
            }
        }

        return null;
    }

    /** @return list<Activity> what the back office may choose */
    public function allowedActivities(): array
    {
        return array_values(array_filter(Activity::cases(), fn (Activity $a) => !$a->isRegulated() || !$this->enabled || $this->isAuthorized()));
    }
}
