<?php

namespace Base\Consulting\Tests\Service;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Base\Consulting\Service\CreditBridge;
use PHPUnit\Framework\TestCase;

final class CreditBridgeTest extends TestCase
{
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
