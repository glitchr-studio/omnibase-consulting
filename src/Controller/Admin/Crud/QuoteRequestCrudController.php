<?php

namespace Base\Consulting\Controller\Admin\Crud;

use Base\Admin\Attribute\AdminAction;
use Base\Admin\Config\Action;
use Base\Admin\Config\Actions;
use Base\Admin\Config\Crud;
use Base\Admin\Controller\AbstractCrudController;
use Base\Admin\Filter\Filters;
use Base\Consulting\Entity\QuoteRequest;
use Base\Consulting\Enum\QuoteStatus;
use Base\Field\AssociationField;
use Base\Field\DateTimeField;
use Base\Field\IdField;
use Base\Field\SelectField;
use Base\Field\TextareaField;
use Base\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The quote requests from the site, read and followed: answered, archived,
 * a note of what was answered. They come from the site's form only.
 */
class QuoteRequestCrudController extends AbstractCrudController
{
    private ?TranslatorInterface $translator = null;

    #[Required]
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return QuoteRequest::class;
    }

    public static function getPreferredIcon(): ?string
    {
        return 'fa-solid fa-file-signature';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('status')->add('offering');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions)->disable(Action::NEW);
        foreach ([Actions::PAGE_INDEX, Actions::PAGE_DETAIL] as $page) {
            $actions->add($page, Action::new('answered', '@consulting.admin.quote.action.answered', 'fa-solid fa-reply')->linkToCrudAction('answered'));
            $actions->add($page, Action::new('archive', '@consulting.admin.quote.action.archive', 'fa-solid fa-box-archive')->linkToCrudAction('archive'));
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield DateTimeField::new('createdAt', '@consulting.admin.quote.created_at')->setDisabled();
        yield SelectField::new('status', '@consulting.admin.quote.status')->setColumns(3); // the enum's cases, each naming itself
        yield AssociationField::new('offering', '@consulting.admin.quote.offering')->setColumns(5)->setRequired(false);
        yield TextField::new('name', '@consulting.admin.quote.name')->setColumns(4)->setDisabled();
        yield TextField::new('email', '@consulting.admin.quote.email')->setColumns(4)->setDisabled();
        yield TextField::new('phone', '@consulting.admin.quote.phone')->setColumns(4)->setDisabled()->hideOnIndex();
        yield TextField::new('organisation', '@consulting.admin.quote.organisation')->setColumns(4)->setDisabled();
        yield TextField::new('participants', '@consulting.admin.quote.participants')->setColumns(4)->setDisabled()->hideOnIndex();
        yield TextField::new('dates', '@consulting.admin.quote.dates')->setColumns(4)->setDisabled()->hideOnIndex();
        yield TextField::new('location', '@consulting.admin.quote.location')->setColumns(4)->setDisabled()->hideOnIndex();
        yield TextField::new('budget', '@consulting.admin.quote.budget')->setColumns(4)->setDisabled()->hideOnIndex();
        yield TextareaField::new('message', '@consulting.admin.quote.message')->setDisabled()->hideOnIndex();
        yield TextareaField::new('notes', '@consulting.admin.quote.notes')->setRequired(false)->hideOnIndex();
    }

    #[AdminAction('/{entityId}/answered')]
    public function answered(Request $request, string $entityId): Response
    {
        return $this->mark($request, $entityId, QuoteStatus::ANSWERED);
    }

    #[AdminAction('/{entityId}/archive')]
    public function archive(Request $request, string $entityId): Response
    {
        return $this->mark($request, $entityId, QuoteStatus::ARCHIVED);
    }

    private function mark(Request $request, string $entityId, QuoteStatus $status): Response
    {
        /** @var QuoteRequest $quote */
        $quote = $this->findEntity($entityId);
        $quote->setStatus($status);
        $this->entityManager->flush();
        $this->addFlash('success', $this->translator?->trans('admin.quote.flash.'.$status->value, ['name' => $quote->getName()], 'consulting') ?? $status->value);
        $back = (string) $request->headers->get('referer');

        return '' !== $back && parse_url($back, \PHP_URL_HOST) === $request->getHost() ? $this->redirect($back) : $this->redirectToIndex();
    }
}
