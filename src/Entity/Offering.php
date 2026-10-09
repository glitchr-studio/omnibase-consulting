<?php

namespace Base\Consulting\Entity;

use Base\Consulting\Enum\Activity;
use Base\Consulting\Repository\OfferingRepository;
use Base\Consulting\Validator\OfferingRules;
use Base\Database\Attribute\Uploader;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A service the site offers: a teachers' workshop, a keynote, a show, a
 * training, a consultation. What it is (its activity),
 * who it is for, how it runs and for how long, its price as it should be
 * printed ("On quote", "From €1,500 excl. VAT"), a picture, and whether a
 * quote may be asked for it. `hours`: a package of hours, the point where
 * omnibase/marketplace's Credits will sell it (Service\CreditBridge).
 */
#[ORM\Entity(repositoryClass: OfferingRepository::class)]
#[ORM\Table(name: 'consulting_offering')]
#[UniqueEntity(fields: ['slug'])]
#[OfferingRules]
class Offering
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected ?string $title = null;

    #[ORM\Column(length: 120, unique: true)]
    protected ?string $slug = null;

    #[ORM\Column(type: 'string', length: 32, enumType: Activity::class)]
    protected Activity $activity = Activity::TRAINING;

    /** A line: the card's text, the page's description. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $summary = null;

    /** HTML (the back office's editor) or plain text. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    /** "Teachers K-8", "Schools and districts", "All audiences". */
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $audience = null;

    /** "On site or online", "Keynote, 60 to 90 minutes". */
    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $format = null;

    /** "Half a day", "1 to 3 days". */
    #[ORM\Column(length: 120, nullable: true)]
    protected ?string $duration = null;

    /** As it should be printed: "On quote", "From €1,500 excl. VAT". */
    #[ORM\Column(length: 120, nullable: true)]
    protected ?string $price = null;

    /** The hours of a package, when it is sold by the hour (marketplace Credits). */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\Positive]
    protected ?int $hours = null;

    /** The languages it is given in: "en, fr, es". */
    #[ORM\Column(length: 60, nullable: true)]
    protected ?string $languages = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Uploader(max_size: '16MB', mime_types: ['image/*'])]
    protected $image = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $quotable = true;

    #[ORM\Column(type: 'integer')]
    protected int $position = 0;

    #[ORM\Column(type: 'boolean')]
    protected bool $visible = true;

    public function __construct(?string $title = null, Activity $activity = Activity::TRAINING)
    {
        $this->activity = $activity;
        $this->setTitle($title);
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getId(): ?int { return $this->id; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self
    {
        $this->title = $title ? trim($title) : null;
        if (null === $this->slug && $this->title) {
            $this->slug = (new AsciiSlugger())->slug($this->title)->lower()->truncate(120)->toString();
        }

        return $this;
    }

    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(?string $slug): self { $this->slug = $slug ? (new AsciiSlugger())->slug($slug)->lower()->truncate(120)->toString() : $this->slug; return $this; }

    public function getActivity(): Activity { return $this->activity; }
    public function setActivity(Activity|string $activity): self { $this->activity = $activity instanceof Activity ? $activity : (Activity::tryFrom($activity) ?? Activity::OTHER); return $this; }

    public function getSummary(): ?string { return $this->summary; }
    public function setSummary(?string $summary): self { $this->summary = $summary ? trim($summary) : null; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description ? trim($description) : null; return $this; }

    /** Whether the description is HTML (from the editor or an import) rather than plain text. */
    public function isHtml(): bool { return (bool) preg_match('#<(p|ul|ol|h[1-6]|div|br|strong|em|a)\b#i', (string) $this->description); }

    public function getAudience(): ?string { return $this->audience; }
    public function setAudience(?string $audience): self { $this->audience = $audience ?: null; return $this; }

    public function getFormat(): ?string { return $this->format; }
    public function setFormat(?string $format): self { $this->format = $format ?: null; return $this; }

    public function getDuration(): ?string { return $this->duration; }
    public function setDuration(?string $duration): self { $this->duration = $duration ?: null; return $this; }

    public function getPrice(): ?string { return $this->price; }
    public function setPrice(?string $price): self { $this->price = $price ?: null; return $this; }

    public function getHours(): ?int { return $this->hours; }
    public function setHours(?int $hours): self { $this->hours = $hours ?: null; return $this; }

    public function getLanguages(): ?string { return $this->languages; }
    public function setLanguages(?string $languages): self { $this->languages = $languages ?: null; return $this; }

    public function getImage(): ?string { return Uploader::getPublic($this, 'image'); }
    public function getImageFile(): ?File { return Uploader::get($this, 'image'); }
    public function setImage($image): self { $this->image = $image; return $this; }

    /** The picture's address on the site ("/uploads/…"). */
    public function getImageUrl(): ?string
    {
        $path = null !== $this->image && '' !== $this->image ? $this->getImage() : null;
        if (!\is_string($path) || '' === $path || preg_match('#^(?:https?:)?//#i', $path)) {
            return $path ?: null;
        }
        $public = strpos($path, '/public/');

        return false !== $public ? substr($path, $public + \strlen('/public')) : $path;
    }

    public function isQuotable(): bool { return $this->quotable; }
    public function setQuotable(bool $quotable): self { $this->quotable = $quotable; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = (int) $position; return $this; }

    public function isVisible(): bool { return $this->visible; }
    public function setVisible(bool $visible): self { $this->visible = $visible; return $this; }

    /** Every word of it, for a rule to read (Offering\OfferingRuleInterface). */
    public function getText(): string
    {
        return trim(implode("\n", array_filter([$this->title, $this->summary, strip_tags((string) $this->description)])));
    }
}
