<?php

namespace Base\Consulting\Entity;

use Base\Consulting\Enum\QuoteStatus;
use Base\Consulting\Repository\QuoteRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A quote asked from the site: for which offering (or none: "something
 * else"), who asks and for which organisation, how many people, when and
 * where, the budget if they say, their message - kept for the back office
 * (new, answered, archived) as long as the form's notice says.
 */
#[ORM\Entity(repositoryClass: QuoteRequestRepository::class)]
#[ORM\Table(name: 'consulting_quote_request')]
#[ORM\Index(columns: ['status', 'createdAt'], name: 'consulting_quote_status_idx')]
class QuoteRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: Offering::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?Offering $offering = null;

    #[ORM\Column(type: 'string', length: 16, enumType: QuoteStatus::class)]
    protected QuoteStatus $status = QuoteStatus::NEW;

    #[ORM\Column(length: 120)]
    protected string $name = '';

    #[ORM\Column(length: 180)]
    protected string $email = '';

    #[ORM\Column(length: 30, nullable: true)]
    protected ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $organisation = null;

    #[ORM\Column(length: 120, nullable: true)]
    protected ?string $participants = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $dates = null;

    #[ORM\Column(length: 255, nullable: true)]
    protected ?string $location = null;

    #[ORM\Column(length: 120, nullable: true)]
    protected ?string $budget = null;

    #[ORM\Column(type: 'text')]
    protected string $message = '';

    #[ORM\Column(length: 5, nullable: true)]
    protected ?string $locale = null;

    /** What the back office wrote down: the answer sent, the price given. */
    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return trim($this->name.($this->organisation ? ' — '.$this->organisation : ''));
    }

    public function getId(): ?int { return $this->id; }

    public function getOffering(): ?Offering { return $this->offering; }
    public function setOffering(?Offering $offering): self { $this->offering = $offering; return $this; }

    public function getStatus(): QuoteStatus { return $this->status; }
    public function setStatus(QuoteStatus|string $status): self { $this->status = $status instanceof QuoteStatus ? $status : (QuoteStatus::tryFrom($status) ?? QuoteStatus::NEW); return $this; }
    /** The status as the back office's select reads and writes it. */
    public function getStatusValue(): string { return $this->status->value; }
    public function setStatusValue(?string $status): self { return $this->setStatus((string) $status); }

    public function getName(): string { return $this->name; }
    public function setName(?string $name): self { $this->name = trim((string) $name); return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = trim((string) $email); return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone ?: null; return $this; }

    public function getOrganisation(): ?string { return $this->organisation; }
    public function setOrganisation(?string $organisation): self { $this->organisation = $organisation ?: null; return $this; }

    public function getParticipants(): ?string { return $this->participants; }
    public function setParticipants(?string $participants): self { $this->participants = $participants ?: null; return $this; }

    public function getDates(): ?string { return $this->dates; }
    public function setDates(?string $dates): self { $this->dates = $dates ?: null; return $this; }

    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): self { $this->location = $location ?: null; return $this; }

    public function getBudget(): ?string { return $this->budget; }
    public function setBudget(?string $budget): self { $this->budget = $budget ?: null; return $this; }

    public function getMessage(): string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = trim((string) $message); return $this; }

    public function getLocale(): ?string { return $this->locale; }
    public function setLocale(?string $locale): self { $this->locale = $locale ? substr($locale, 0, 5) : null; return $this; }

    public function getNotes(): ?string { return $this->notes; }
    public function setNotes(?string $notes): self { $this->notes = $notes ?: null; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
