<?php

namespace Base\Consulting\Twig;

use Base\Consulting\Entity\Offering;
use Base\Consulting\Repository\OfferingRepository;
use Base\Consulting\Service\CreditBridge;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** What a host's own pages ask: the offerings for a home page, whether one can be paid by the hour. */
final class ConsultingExtension extends AbstractExtension
{
    public function __construct(
        private readonly OfferingRepository $offerings,
        private readonly CreditBridge $credits,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('consulting_offerings', function (?int $limit = null): array {
                try {
                    return $this->offerings->findVisible($limit);
                } catch (\Doctrine\DBAL\Exception) {
                    return []; // not migrated yet
                }
            }),
            new TwigFunction('consulting_can_pay', fn (Offering $offering): bool => $this->credits->canSell($offering)),
        ];
    }
}
