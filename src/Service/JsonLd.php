<?php

namespace Base\Consulting\Service;

use Base\Consulting\Entity\Offering;
use Base\Service\SettingBagInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** The offerings as schema.org reads them: a Service each, an OfferCatalog for the page. */
final class JsonLd
{
    public function __construct(
        private readonly UrlGeneratorInterface $urls,
        private readonly ?SettingBagInterface $settings = null,
        #[Autowire('%consulting.provider%')] private readonly ?string $provider = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function service(Offering $offering, bool $context = true): array
    {
        return array_filter([
            '@context' => $context ? 'https://schema.org' : null,
            '@type' => 'Service',
            'name' => $offering->getTitle(),
            'description' => $offering->getSummary() ?? (mb_substr(strip_tags((string) $offering->getDescription()), 0, 300) ?: null),
            'serviceType' => $offering->getActivity()->value,
            'audience' => $offering->getAudience() ? ['@type' => 'Audience', 'audienceType' => $offering->getAudience()] : null,
            'availableLanguage' => $offering->getLanguages() ? array_map('trim', explode(',', $offering->getLanguages())) : null,
            'provider' => ($name = $this->providerName()) ? ['@type' => 'Person', 'name' => $name] : null,
            'url' => $offering->getSlug() ? $this->urls->generate('consulting_offering', ['slug' => $offering->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL) : null,
        ], static fn ($v) => null !== $v && [] !== $v);
    }

    /**
     * @param list<Offering> $offerings
     *
     * @return array<string, mixed>
     */
    public function catalog(array $offerings): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'OfferCatalog',
            'name' => $this->providerName(),
            'itemListElement' => array_map(fn (Offering $o) => ['@type' => 'Offer', 'itemOffered' => $this->service($o, false)], $offerings),
        ];
    }

    private function providerName(): ?string
    {
        if ($this->provider) {
            return $this->provider;
        }
        try {
            $title = $this->settings?->getScalar('base.settings.title');
        } catch (\Throwable) {
            $title = null;
        }

        return \is_string($title) && '' !== trim($title) ? $title : null;
    }
}
