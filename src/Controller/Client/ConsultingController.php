<?php

namespace Base\Consulting\Controller\Client;

use Base\Attributes\Attribute\Sitemap;
use Base\Consulting\Entity\Offering;
use Base\Consulting\Form\Model\QuoteRequestModel;
use Base\Consulting\Form\Type\QuoteRequestType;
use Base\Consulting\Repository\OfferingRepository;
use Base\Consulting\Service\CreditBridge;
use Base\Consulting\Service\JsonLd;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The services: what is offered (/services), each one's page, and the quote
 * request - omnibase's contact form with what a quote needs, kept for the
 * back office and sent to the site's address. A robot that fills the trap
 * is thanked and nothing is kept.
 */
class ConsultingController extends AbstractController
{
    public function __construct(
        private readonly OfferingRepository $offerings,
        private readonly CreditBridge $credits,
        private readonly JsonLd $jsonLd,
        #[Autowire('%consulting.retention_months%')] private readonly int $retention = 36,
    ) {
    }

    #[Sitemap(priority: 0.8, changefreq: 'monthly')]
    #[Route('/services', name: 'consulting_offerings', methods: ['GET'])]
    public function offerings(): Response
    {
        $offerings = $this->offerings->findVisible();

        return $this->render('@Consulting/client/offerings.html.twig', [
            'offerings' => $offerings,
            'jsonld' => $offerings ? $this->jsonLd->catalog($offerings) : null,
        ]);
    }

    #[Route('/services/{slug}', name: 'consulting_offering', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function offering(string $slug): Response
    {
        $offering = $this->find($slug);

        return $this->render('@Consulting/client/offering.html.twig', [
            'offering' => $offering,
            'can_pay' => $this->credits->canSell($offering),
            'others' => array_values(array_filter($this->offerings->findVisible(), fn (Offering $o) => $o->getId() !== $offering->getId())),
            'jsonld' => $this->jsonLd->service($offering),
        ]);
    }

    #[Route('/quote', name: 'consulting_quote', methods: ['GET', 'POST'])]
    public function quote(
        Request $request,
        FormFactoryInterface $forms,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        #[Autowire('%consulting.recipient%')] string $recipient,
    ): Response {
        $model = new QuoteRequestModel();
        $slug = $request->query->getString('offering');
        if ('' !== $slug && $offering = $this->offerings->findOneVisible($slug)) {
            $model->offering = $offering;
        }
        $form = $forms->createNamed('quote', QuoteRequestType::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A filled trap is a robot: thanked all the same, nothing kept nor sent.
            if (!$model->isRobot()) {
                $quote = $model->toQuoteRequest($request->getLocale());
                $entityManager->persist($quote);
                $entityManager->flush();
                $mailer->send((new TemplatedEmail())
                    ->to($recipient)
                    ->replyTo(new Address((string) $model->email, (string) $model->name))
                    ->subject($translator->trans('quote.mail.subject', ['name' => $model->name, 'offering' => $model->offering?->getTitle() ?? '—'], 'consulting'))
                    ->htmlTemplate('@Consulting/email/quote.html.twig')
                    ->context(['quote' => $quote]));
            }

            return $this->render('@Consulting/client/quote_sent.html.twig', ['quote' => $model]);
        }

        return $this->render('@Consulting/client/quote.html.twig', [
            'form' => $form,
            'offering' => $model->offering,
            'retention' => $this->retention,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }

    private function find(string $slug): Offering
    {
        return $this->offerings->findOneVisible($slug) ?? throw $this->createNotFoundException(sprintf('No offering "%s".', $slug));
    }
}
