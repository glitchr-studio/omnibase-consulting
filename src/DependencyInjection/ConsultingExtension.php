<?php

namespace Base\Consulting\DependencyInjection;

use Base\Bundle\AbstractBaseExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ConsultingExtension extends AbstractBaseExtension
{
    public function getConfiguration(array $config, ContainerBuilder $container): ConsultingConfiguration
    {
        return new ConsultingConfiguration();
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');

        $configuration = new ConsultingConfiguration();
        $config = (new Processor())->processConfiguration($configuration, $configs);

        // Flat parameters: consulting.recipient, consulting.regulated_activity_guard...
        $this->setConfiguration($container, $config, $configuration->getTreeBuilder()->buildTree()->getName());
    }
}
