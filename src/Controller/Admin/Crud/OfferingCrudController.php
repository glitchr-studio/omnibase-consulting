<?php

namespace Base\Consulting\Controller\Admin\Crud;

use Base\Admin\Attribute\OpenToAdmins;
use Base\Admin\Controller\AbstractCrudController;
use Base\Consulting\Entity\Offering;
use Base\Consulting\Enum\Activity;
use Base\Consulting\Service\RegulatedActivityGuard;
use Base\Field\BooleanField;
use Base\Field\EditorField;
use Base\Field\IdField;
use Base\Field\ImageField;
use Base\Field\IntegerField;
use Base\Field\SelectField;
use Base\Field\SlugField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * What the site offers: each service's activity (a reserved one is not
 * offered when the RegulatedActivityGuard is on), its texts, for whom, how,
 * how long, its price as printed, a package of hours, a picture, whether a
 * quote may be asked.
 */
#[OpenToAdmins]
class OfferingCrudController extends AbstractCrudController
{
    private ?TranslatorInterface $translator = null;
    private ?RegulatedActivityGuard $guard = null;

    #[Required]
    public function setConsultingServices(TranslatorInterface $translator, RegulatedActivityGuard $guard): void
    {
        $this->translator = $translator;
        $this->guard = $guard;
    }

    public static function getEntityFqcn(): string
    {
        return Offering::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-handshake';
    }

    public function configureFields(string $pageName): iterable
    {
        $activities = [];
        foreach ($this->guard?->allowedActivities() ?? [] as $activity) {
            $activities[$this->translator?->trans($activity->label(), [], 'consulting') ?? $activity->value] = $activity->value;
        }

        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title', '@consulting.admin.offering.title')->setColumns(8);
        // The enum's cases, among those the guard allows: the record receives the case.
        $activity = SelectField::new('activity', '@consulting.admin.offering.activity')->setClass(Activity::class)->setChoices($activities)->setColumns(4);
        yield $this->guard?->isEnabled() ? $activity->setHelp('@consulting.admin.offering.activity_guarded') : $activity;
        yield SlugField::new('slug')->setColumns(6)->hideOnIndex();
        yield TextareaField::new('summary', '@consulting.admin.offering.summary')->setRequired(false)->hideOnIndex();
        yield EditorField::new('description', '@consulting.admin.offering.description')->setRequired(false)->hideOnIndex();
        yield TextField::new('audience', '@consulting.admin.offering.audience')->setColumns(6)->setRequired(false)->hideOnIndex();
        yield TextField::new('format', '@consulting.admin.offering.format')->setColumns(6)->setRequired(false)->hideOnIndex();
        yield TextField::new('duration', '@consulting.admin.offering.duration')->setColumns(4)->setRequired(false)->hideOnIndex();
        yield TextField::new('price', '@consulting.admin.offering.price')->setColumns(4)->setRequired(false)->setHelp('@consulting.admin.offering.price_help');
        yield IntegerField::new('hours', '@consulting.admin.offering.hours')->setColumns(4)->setRequired(false)->hideOnIndex()->setHelp('@consulting.admin.offering.hours_help');
        yield TextField::new('languages', '@consulting.admin.offering.languages')->setColumns(4)->setRequired(false)->hideOnIndex();
        yield ImageField::new('image', '@consulting.admin.offering.image')->setColumns(6)->hideOnIndex();
        yield IntegerField::new('position', '@consulting.admin.offering.position')->setColumns(2);
        yield BooleanField::new('quotable', '@consulting.admin.offering.quotable')->setColumns(3);
        yield BooleanField::new('visible', '@consulting.admin.offering.visible')->setColumns(3);
    }
}
