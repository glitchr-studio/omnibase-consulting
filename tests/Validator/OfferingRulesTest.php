<?php

namespace Base\Consulting\Tests\Validator;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Base\Consulting\Offering\OfferingRuleInterface;
use Base\Consulting\Offering\OfferingViolation;
use Base\Consulting\Validator\OfferingRules;
use Base\Consulting\Validator\OfferingRulesValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

/**
 * An offering keeps the rules the site has, which this bundle does not
 * know: none, it is saved; one that refuses it, the reason is printed under
 * the field the rule names. Nothing in the bundle itself refuses an
 * activity: what the law reserves to a regulated profession is a jurist's
 * profile's (omnibase/legal).
 */
final class OfferingRulesTest extends TestCase
{
    public function testWithoutRulesEveryOfferingValidates(): void
    {
        $offering = (new Offering('Consultation', Activity::CONSULTING))->setSummary('Une consultation juridique personnalisée sur votre dossier.');

        self::assertCount(0, $this->validator()->validate($offering, new OfferingRules()));
    }

    public function testARuleRefusesAnOfferingUnderTheFieldItNames(): void
    {
        $rule = new class implements OfferingRuleInterface {
            public function check(Offering $offering): ?OfferingViolation
            {
                return str_contains((string) $offering->getSummary(), 'garanti') ? new OfferingViolation('Rien n’est garanti.', 'summary') : null;
            }
        };

        $refused = $this->validator($rule)->validate((new Offering('Atelier'))->setSummary('Succès garanti.'), new OfferingRules());
        self::assertCount(1, $refused);
        self::assertSame('summary', $refused[0]->getPropertyPath());
        self::assertSame('Rien n’est garanti.', $refused[0]->getMessage());

        self::assertCount(0, $this->validator($rule)->validate((new Offering('Atelier'))->setSummary('Une demi-journée.'), new OfferingRules()));
    }

    public function testEveryRuleIsAsked(): void
    {
        $no = static fn (string $path) => new class($path) implements OfferingRuleInterface {
            public function __construct(private readonly string $path) {}
            public function check(Offering $offering): ?OfferingViolation { return new OfferingViolation('Non.', $this->path); }
        };

        self::assertCount(2, $this->validator($no('title'), $no('description'))->validate(new Offering('Atelier'), new OfferingRules()));
    }

    public function testNoActivityIsReservedHere(): void
    {
        self::assertSame(['training', 'talk', 'consulting', 'information', 'writing', 'other'], array_map(static fn (Activity $a) => $a->value, Activity::cases()));
        self::assertSame(Activity::OTHER, (new Offering('x'))->setActivity('legal_consultation')->getActivity(), 'a word of the former reserved cases is read as other');
    }

    private function validator(OfferingRuleInterface ...$rules): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        $factory = new class(new OfferingRulesValidator($rules)) extends ConstraintValidatorFactory {
            public function __construct(OfferingRulesValidator $validator)
            {
                parent::__construct();
                $this->validators[OfferingRulesValidator::class] = $validator;
            }
        };

        // The constraint alone: the entity's others (UniqueEntity) need Doctrine.
        return Validation::createValidatorBuilder()->setConstraintValidatorFactory($factory)->getValidator();
    }
}
