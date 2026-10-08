<?php

namespace Base\Consulting;

use Base\Bundle\AbstractBaseBundle;
use Base\Traits\SingletonTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * An independent professional's services on their own site: what they
 * offer (Offering: a workshop, a keynote, a training, a consultation - who
 * it is for, how it runs, its price as it should be printed), the quote
 * request on omnibase's contact model (QuoteRequest, kept and answered from
 * the back office), and, when the site's profession calls for it, the
 * RegulatedActivityGuard: no offering of what only a regulated profession
 * may do. Hours paid in advance will be omnibase/marketplace Credits.
 */
class ConsultingBundle extends AbstractBaseBundle
{
    use SingletonTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /** Modern layout: the class lives in src/, the bundle root is the package root. */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->setMapping($this->getPath().'/src/Entity', 'Base\Consulting\Entity', 'App\Entity\Consulting');
        $this->setMapping($this->getPath().'/src/Repository', 'Base\Consulting\Repository', 'App\Repository\Consulting');
    }
}
