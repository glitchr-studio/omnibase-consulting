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
                ->booleanNode('regulated_activity_guard')->defaultFalse()
                    ->info('Refuse an offering of what only a regulated profession may do (French law of 31 December 1971, art. 54 ff.: personalised legal advice, drafting deeds for others) unless authorized_status says the site may.')->end()
                ->scalarNode('authorized_status')->defaultNull()
                    ->validate()->ifTrue(static fn ($v) => null !== $v && !\in_array($v, ['lawyer', 'notary', 'teacher', 'regulated_profession'], true))->thenInvalid('authorized_status: lawyer, notary, teacher, regulated_profession or null.')->end()
                    ->info('The status that lets the site offer legal advice (art. 54 and 56 to 66-1): a lawyer, a notary, a university teacher (art. 57), another regulated profession within its own field (art. 59).')->end()
                ->integerNode('retention_months')->min(1)->defaultValue(36)
                    ->info('How long a quote request is kept, as the form\'s notice says.')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
