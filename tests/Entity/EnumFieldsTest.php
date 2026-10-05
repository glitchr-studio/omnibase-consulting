<?php

namespace Base\Consulting\Tests\Entity;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Entity\QuoteRequest;
use Base\Consulting\Enum\Activity;
use Base\Consulting\Enum\QuoteStatus;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * An offering's activity and a quote request's status are PHP enums on their
 * string columns (Doctrine's enumType): the record holds the case, a setter
 * still takes the stored word, and a case names itself for the back
 * office's select.
 */
final class EnumFieldsTest extends TestCase
{
    public function testAnOfferingHoldsItsActivity(): void
    {
        $this->assertSame(Activity::TRAINING, (new Offering('Atelier'))->getActivity());

        $offering = new Offering('Conférence', Activity::TALK);
        $this->assertSame(Activity::TALK, $offering->getActivity());
        $this->assertSame(Activity::WRITING, $offering->setActivity('writing')->getActivity(), 'the stored word is read as its case');
        $this->assertSame(Activity::OTHER, $offering->setActivity('nonsense')->getActivity(), 'a word that names no case: other');
    }

    public function testAQuoteRequestHoldsItsStatus(): void
    {
        $quote = new QuoteRequest();
        $this->assertSame(QuoteStatus::NEW, $quote->getStatus());
        $this->assertSame(QuoteStatus::ANSWERED, $quote->setStatus('answered')->getStatus());
    }

    public function testTheColumnsAreStringsOfTheSameLengthMappedToTheEnum(): void
    {
        foreach ([[Offering::class, 'activity', Activity::class, 32], [QuoteRequest::class, 'status', QuoteStatus::class, 16]] as [$class, $property, $enum, $length]) {
            $column = (new \ReflectionProperty($class, $property))->getAttributes(ORM\Column::class)[0]->newInstance();
            $this->assertSame('string', $column->type, "$class::$property");
            $this->assertSame($length, $column->length, "$class::$property: the column it always had");
            $this->assertSame($enum, $column->enumType, "$class::$property");
        }
    }

    public function testACaseNamesItselfInTheConsultingDomain(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')->with('activity.talk', [], 'consulting', 'fr')->willReturn('Conférence');

        $this->assertInstanceOf(TranslatableInterface::class, Activity::TALK);
        $this->assertSame('Conférence', Activity::TALK->trans($translator, 'fr'));
        $this->assertInstanceOf(TranslatableInterface::class, QuoteStatus::NEW);
    }
}
