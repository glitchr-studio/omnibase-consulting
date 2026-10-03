<?php

namespace Base\Consulting\Admin\Widget;

use Base\Admin\Config\Menu\MenuItem;
use Base\Admin\Widget\DashboardWidgetTypeInterface;
use Base\Consulting\Repository\QuoteRequestRepository;

/**
 * The dashboard's "Quote requests": how many wait for an answer, the
 * latest five. `yield MenuItem::block('consulting_quotes', ...)` places it.
 */
final class QuoteRequestsWidgetType implements DashboardWidgetTypeInterface
{
    public function __construct(private readonly QuoteRequestRepository $quotes)
    {
    }

    public static function getName(): string
    {
        return 'consulting_quotes';
    }

    public function getTemplate(): string
    {
        return '@Consulting/admin/widget/quotes.html.twig';
    }

    public function getTemplateVars(MenuItem $widget): array
    {
        return [
            'count' => $this->quotes->countNew(),
            'quotes' => $this->quotes->findNew(5),
        ];
    }
}
