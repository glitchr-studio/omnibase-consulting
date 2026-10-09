<?php

namespace Base\Consulting\Tests\Form;

use Base\Consulting\Form\Model\QuoteRequestModel;
use Base\Consulting\Form\Type\QuoteRequestType;
use Base\Service\FormGuard;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The quote request is guarded as glitchr/omnibase guards a form (option `guard`, action "quote"), in
 * place of the contact form's own `website` trap: a filled trap, a form sent faster than its delay and
 * a missing captcha token are refused on the form; a request sent as a person sends it goes through.
 * Without a captcha (no glitchr/omnishield, or none configured: `challenge: false`), the trap and the
 * time alone. Run by a host application's PHPUnit, its test environment's captcha being omnishield's
 * "fixed" gateway (token omnishield-fixed-token).
 */
final class QuoteRequestGuardTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $_SERVER['KERNEL_CLASS'] ??= $_ENV['KERNEL_CLASS'] ?? 'App\\Kernel';
        if (!class_exists($_SERVER['KERNEL_CLASS'])) {
            self::markTestSkipped('Needs a host application (its kernel).');
        }
        if (!class_exists(FormGuard::class)) {
            self::markTestSkipped('Needs a glitchr/omnibase with the forms\' guard.');
        }
        self::bootKernel();
        static::getContainer()->get('request_stack')->push(Request::create('https://localhost/quote', 'POST', server: ['REMOTE_ADDR' => '203.0.113.9']));
    }

    /** @param array<string, mixed> $guard */
    private function form(array $guard = []): FormInterface
    {
        $options = ['csrf_protection' => false];
        if ($guard) {
            $options['guard'] = $guard + ['action' => 'quote'];
        }

        return static::getContainer()->get('form.factory')->createNamed('quote', QuoteRequestType::class, new QuoteRequestModel(), $options);
    }

    /** @param array<string, string> $overrides */
    private function send(FormInterface $form, array $overrides = []): FormInterface
    {
        $data = [
            'name' => 'Camille Weber',
            'email' => 'camille@example.org',
            'message' => 'Une journée de formation pour une équipe de trente personnes.',
            'consent' => '1',
            'guard_website' => '',
            'guard_opened' => static::getContainer()->get(FormGuard::class)->stamp(time() - 10),
        ];
        if ($form->has('guard_captcha')) {
            $data['guard_captcha'] = (class_exists(\Omnishield\Testing\FixedGateway::class) ? \Omnishield\Testing\FixedGateway::TOKEN : 'omniguard-fixed-token'); // omnishield's "fixed" test gateway, or omniguard's on a host not moved to omnishield yet.
        }
        $form->submit(array_filter($overrides + $data, static fn ($value) => null !== $value));

        return $form;
    }

    /** @return list<string> what refused the form: the guard's reasons, or the field with an error */
    private function refusals(FormInterface $form): array
    {
        $found = [];
        foreach ($form->getErrors(true) as $error) {
            $found[] = \is_string($error->getCause()) ? $error->getCause() : $error->getOrigin()?->getName();
        }

        return $found;
    }

    public function testTheFormIsGuardedInPlaceOfItsOwnTrap(): void
    {
        $form = $this->form();
        self::assertSame('quote', $form->getConfig()->getOption('guard')['action']);
        self::assertFalse($form->has('website'), 'the contact form\'s own trap is gone');
        self::assertTrue($form->has(FormGuard::TRAP_FIELD));
        self::assertTrue($form->has(FormGuard::STAMP_FIELD));
    }

    public function testARequestSentAsAPersonSendsItGoesThrough(): void
    {
        $form = $this->send($this->form());
        self::assertTrue($form->isValid(), implode(', ', $this->refusals($form)));
    }

    public function testAFilledTrapIsRefused(): void
    {
        $form = $this->send($this->form(), ['guard_website' => 'https://spam.example']);
        self::assertFalse($form->isValid());
        self::assertContains(FormGuard::TRAPPED, $this->refusals($form));
    }

    public function testARequestSentTooFastIsRefused(): void
    {
        $form = $this->send($this->form(['min_delay' => 5]), ['guard_opened' => static::getContainer()->get(FormGuard::class)->stamp(time() - 1)]);
        self::assertFalse($form->isValid());
        self::assertContains(FormGuard::TOO_FAST, $this->refusals($form));
    }

    public function testARequestWithoutTheCaptchasTokenIsRefused(): void
    {
        $form = $this->form();
        if (!$form->has('guard_captcha')) {
            self::markTestSkipped('The host application has no captcha (glitchr/omnishield).');
        }
        $this->send($form, ['guard_captcha' => '']);
        self::assertFalse($form->isValid());
        self::assertTrue($form->get('guard_captcha')->getErrors()->count() > 0, 'refused on the captcha');
    }

    public function testWithoutACaptchaTheTrapAndTheTimeAlone(): void
    {
        $form = $this->send($this->form(['challenge' => false]));
        self::assertFalse($form->has('guard_captcha'));
        self::assertTrue($form->isValid(), implode(', ', $this->refusals($form)));

        self::assertFalse($this->send($this->form(['challenge' => false]), ['guard_website' => 'x'])->isValid(), 'the trap still holds');
        self::assertFalse($this->send($this->form(['challenge' => false, 'min_delay' => 5]), ['guard_opened' => static::getContainer()->get(FormGuard::class)->stamp(time())])->isValid(), 'the time still holds');
    }
}
