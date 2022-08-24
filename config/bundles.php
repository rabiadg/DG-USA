<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Sonata\AdminBundle\SonataAdminBundle::class => ['all' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
    Sonata\BlockBundle\SonataBlockBundle::class => ['all' => true],
    Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle::class => ['all' => true],
    Sonata\Form\Bridge\Symfony\SonataFormBundle::class => ['all' => true],
    Sonata\Twig\Bridge\Symfony\SonataTwigBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle::class => ['all' => true],
    Sonata\UserBundle\SonataUserBundle::class => ['all' => true],
    App\Application\Sonata\UserBundle\ApplicationSonataUserBundle::class => ['all' => true],
    Sonata\MediaBundle\SonataMediaBundle::class => ['all' => true],
    App\Application\Sonata\MediaBundle\ApplicationSonataMediaBundle::class => ['all' => true],
    Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle::class => ['all' => true],
    Sonata\SeoBundle\SonataSeoBundle::class => ['all' => true],
    Sonata\PageBundle\SonataPageBundle::class => ['all' => true],
    App\Application\Sonata\PageBundle\ApplicationSonataPageBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    App\Application\CMS\MenuBundle\ApplicationCMSMenuBundle::class => ['all' => true],
    Sonata\Exporter\Bridge\Symfony\SonataExporterBundle::class => ['all' => true],
    FOS\CKEditorBundle\FOSCKEditorBundle::class => ['all' => true],
];
