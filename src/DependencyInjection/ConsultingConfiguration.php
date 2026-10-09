<?php

namespace Base\Consulting\DependencyInjection;

use Base\Bundle\AbstractBaseConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConsultingConfiguration extends AbstractBaseConfiguration
{
    private bool $childrenDeclared = false;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = $this->getTreeBuilder();
        if ($this->childrenDeclared) {
            return $treeBuilder;
        }
        $this->childrenDeclared = true;

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('recipient')->defaultValue('%env(MAILER_CONTACT)%')
                    ->info('Where a quote request is sent.')->end()
                ->scalarNode('provider')->defaultNull()
                    ->info('Who provides the services, for schema.org (a name); null: the site\'s title.')->end()
                // The guard on what the law of 31 December 1971 reserves is a jurist's, in omnibase/legal since 2026-10-09
                // (legal.profession: jurist). The two keys are read for one version: off, they mean what they always did.
                ->booleanNode('regulated_activity_guard')->defaultFalse()
                    ->setDeprecated('omnibase/consulting', '1.1', 'The "%node%" option is gone: a jurist\'s guard on legal advice is omnibase/legal\'s (legal.profession: jurist). Remove it.')
                    ->validate()->ifTrue(static fn ($v) => true === $v)->thenInvalid('consulting.regulated_activity_guard: the guard on what the law of 31 December 1971 reserves moved to omnibase/legal - install it with legal.profession: jurist, and remove this option.')->end()
                ->end()
                ->scalarNode('authorized_status')->defaultNull()
                    ->setDeprecated('omnibase/consulting', '1.1', 'The "%node%" option is gone: it is legal.jurist.authorized_status in omnibase/legal. Remove it.')
                    ->validate()->ifTrue(static fn ($v) => null !== $v)->thenInvalid('consulting.authorized_status moved to omnibase/legal: legal.jurist.authorized_status.')->end()
                ->end()
                ->integerNode('retention_months')->min(1)->defaultValue(36)
                    ->info('How long a quote request is kept, as the form\'s notice says.')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
