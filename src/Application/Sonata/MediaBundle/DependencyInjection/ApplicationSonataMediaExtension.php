<?php

namespace App\Application\Sonata\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ApplicationSonataMediaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $xmlloader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));


        $loader->load('svgprovider.yml');
        $xmlloader->load('provider.xml');
        //$xmlloader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $phploader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$phploader->load('doctrine_orm_admin.php');
        //$xmlloader->load('provider.xml');


    }
}
