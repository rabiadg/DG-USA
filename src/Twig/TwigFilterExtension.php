<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use App\Entity\HomePageSlider;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigFilterExtension extends AbstractExtension
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;

    }

    public function getFilters()
    {
        return [
            new TwigFilter('getSites', [$this, 'getSites']),
            new TwigFilter('getMenuByAlias', [$this, 'getMenuByAlias']),
            new TwigFilter('Settings', [$this, 'Settings']),

        ];
    }

    public function getSites()
    {
        $cms_base_controller = $this->container->get('cms.base_controller');
        return $cms_base_controller->getSites();
    }

    public function getMenuByAlias($alias)
    {
        $cms_crud_controller = $this->container->get('cms.base_controller');
        return $cms_crud_controller->getMenuByAlias($alias);
    }

    public function Settings()
    {
        $cms_crud_controller = $this->container->get('cms.base_controller');
        return $cms_crud_controller->getSettings();
    }
}