<?php

namespace Base\Consulting\Tests\Service;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Base\Consulting\Service\CreditBridge;
use Base\Consulting\Service\RegulatedActivityGuard;
use PHPUnit\Framework\TestCase;

final class RegulatedActivityGuardTest extends TestCase
{
    public function testOffByDefault(): void
    {
        $guard = new RegulatedActivityGuard();
        $this->assertFalse($guard->isEnabled());
        $this->assertNull($guard->check(new Offering('Consultation juridique', Activity::LEGAL_CONSULTATION)));
        $this->assertCount(\count(Activity::cases()), $guard->allowedActivities());
    }

    public function testOnItKeepsTheReservedActivitiesOut(): void
    {
        $guard = new RegulatedActivityGuard(true);

        $this->assertSame('guard.legal_consultation', $guard->check(new Offering('Un rendez-vous', Activity::LEGAL_CONSULTATION)));
        $this->assertSame('guard.deed_drafting', $guard->check(new Offering('Vos statuts', Activity::DEED_DRAFTING)));
        // Filed as a training, sold as advice: the words give it away.
        $this->assertSame('guard.wording', $guard->check((new Offering('Atelier droit du travail', Activity::TRAINING))->setSummary('Une consultation juridique personnalisée sur votre dossier.')));
        $this->assertSame('guard.wording', $guard->check((new Offering('Legal clinic', Activity::CONSULTING))->setDescription('<p>Personalised <b>legal advice</b> on your case.</p>')));
        // Training and documentary information are open to all (art. 66-1).
        $this->assertNull($guard->check((new Offering('Le droit des contrats pour les associations', Activity::TRAINING))->setSummary('Une formation de deux jours, cas pratiques et documentation.')));
        $this->assertNull($guard->check(new Offering('Veille juridique', Activity::INFORMATION)));
        $this->assertNotContains(Activity::LEGAL_CONSULTATION, $guard->allowedActivities());
    }

    public function testAnAuthorizedStatusLiftsIt(): void
    {
        $guard = new RegulatedActivityGuard(true, 'teacher');

        $this->assertTrue($guard->isAuthorized());
        $this->assertNull($guard->check(new Offering('Consultation', Activity::LEGAL_CONSULTATION)));
        $this->assertContains(Activity::LEGAL_CONSULTATION, $guard->allowedActivities());
    }

    public function testNoHoursAreSoldBeforeTheMarketplaceHasCredits(): void
    {
        $bridge = new CreditBridge();
        $offering = (new Offering('Coaching', Activity::CONSULTING))->setHours(10);

        $this->assertSame(class_exists(CreditBridge::CREDIT_CLASS), $bridge->isAvailable());
        $this->assertSame($bridge->isAvailable(), $bridge->canSell($offering));
        $this->assertFalse($bridge->canSell(new Offering('Keynote', Activity::TALK)), 'no package of hours, nothing to sell');
        $this->assertSame('coaching', $offering->getSlug());
    }
}
