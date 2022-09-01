<?php

namespace App\Application\Sonata\PageBundle\Controller;

use App\Application\Sonata\PageBundle\CmsManager\CustomCmsPageManager;
use Buzz\Message\Response;
use Sonata\PageBundle\Page\PageServiceManager;
use Sonata\SeoBundle\Seo\SeoPage;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \App\Controller\BaseController;

class DefaultController extends BaseController
{

    protected $defaultSite = false;

    protected $cmsPageManager;
    protected $pageManager;
    protected $seoPage;

    public function __construct(CustomCmsPageManager $cmsPageManager, PageServiceManager $pageManager, SeoPage $seoPage)
    {
        $this->cmsPageManager = $cmsPageManager;
        $this->pageManager = $pageManager;
        $this->seoPage = $seoPage;
    }

    public function indexAction($path)
    {
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if (strpos($path, 'ar/') !== false) {
            $path = str_replace('ar/', '', $path);
            $request->setLocale('ar');
        }
        if ($path == '/ar' and strpos($path, 'ar') !== false) {
            $path = str_replace('ar', '', $path);
            $request->setLocale('ar');
        }

        $site = $this->getSiteByLocale();
        //$cmsPageManager = $this->container->get('sonata.page.custom_cms_page');
        $cmsPageManager = $this->cmsPageManager;
        $page = $cmsPageManager->getPage($site, $path);
        //$pageManager = $this->getContainer()->get('sonata.page.page_service_manager');
        $pageManager = $this->pageManager;
        $this->get('twig')->addGlobal('page', $page);
        //$this->get('twig')->addGlobal('HeaderMenu', $this->getMenuById(1));
        //$this->get('twig')->addGlobal('FooterMenu', $this->getMenuById(2));
        //$this->get('twig')->addGlobal('settings', $this->getSettings());
        //$seoPage = $this->getContainer()->get('sonata.seo.page');
        $seoPage = $this->seoPage;
        $seoPage
            ->addMeta('property', 'og:site_name', $page->getTitle() ?? '')
            ->addMeta('property', 'og:description', $page->getMetaDescription() ?? '');
        return $pageManager->execute($page, $request, array(), null);
    }

    public function getSiteByLocale($locale = false)
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $DM = $this->getDoctrineManager();
        if (!$locale) {

            if (!$this->defaultSite) {
                return $this->defaultSite = $DM->getRepository('App\Application\Sonata\PageBundle\Entity\Site')
                    ->findOneBy(array('locale' => $request->getLocale()));
            } else {
                return $this->defaultSite;
            }

        } else {
            return $site = $DM->getRepository('App\Application\Sonata\PageBundle\Entity\Site')
                ->findOneBy(array('locale' => $locale));
        }

    }


}
