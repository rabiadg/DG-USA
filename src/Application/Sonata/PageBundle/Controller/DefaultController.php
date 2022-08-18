<?php

namespace App\Application\Sonata\PageBundle\Controller;

use Buzz\Message\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \App\Controller\BaseController;

class DefaultController extends BaseController
{

    protected $defaultSite = false;

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
        $cmsPageManager = $this->getContainer()->get('sonata.page.cms.page');
        $page = $cmsPageManager->getPage($site, $path);
        $pageManager = $this->getContainer()->get('sonata.page.page_service_manager');
        $this->get('twig')->addGlobal('page', $page);
        $this->get('twig')->addGlobal('HeaderMenu', $this->getMenuById(1));
        $this->get('twig')->addGlobal('FooterMenu', $this->getMenuById(2));
        $this->get('twig')->addGlobal('settings', $this->getSettings());
        $seoPage = $this->getContainer()->get('sonata.seo.page');
        $seoPage
            ->addMeta('property', 'og:site_name', $page->getTitle())
            ->addMeta('property', 'og:description', $page->getMetaDescription());
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

    public function pageByRevAction($id, $rev)
    {
        $em = $this->getDoctrineManager();
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $class = "App\Application\Sonata\PageBundle\Entity\Page";
        $manager = $this->getContainer()->get('sonata.admin.audit.manager.do-not-use');
        if (!$manager->hasReader($class)) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the audit reader for class : %s',
                $this->admin->getClass()
            ));
        }
        $reader = $manager->getReader($class);
        // retrieve the revisioned object
        $object = $reader->find($class, $id, $rev);
        $page = $em->getRepository($class)->findOneBy(['id' => $id], ['position' => 'ASC']);
        if (!$object) {
            throw $this->createNotFoundException(sprintf(
                'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
                $id,
                $revision,
                $class
            ));
        }

        $pageManager = $this->getContainer()->get('sonata.page.page_service_manager');

        $seoPage = $this->getContainer()->get('sonata.seo.page');
        $seoPage
            ->addMeta('property', 'og:site_name', $object->getTitle())
            ->addMeta('property', 'og:description', $object->getMetaDescription());

        $dupe_field_date = array();
        $dupe_field_type = array();
        foreach ($object->getBlocks() as $block) {
            $sqlQuery = "SELECT MAX(t.`updated_at`) FROM page__block_audit t WHERE t.`type`='" . $block->getType() . "' and t.id='".$block->getId()."' and t.rev<=$rev and t.page_id=" . $id;
            $DATE = $this->doctrineNativQueryOneRow($sqlQuery);
            if (!in_array($DATE, $dupe_field_date)) {
                $dupe_field_date[] = $DATE;
                $dupe_field_type[] = $block->getType();
            }

        }
        //dump($dupe_field_date);die('call');
        $allBlocks = array();


        if (count($object->getBlocks()) > 0) {
            foreach ($object->getBlocks() as $block) {
                if (in_array($block->getUpdatedAt()->format('Y-m-d H:i:s'), $dupe_field_date) and $block->getType() != 'sonata.page.block.container') {
                    $allBlocks[] = $block;
                }
            }
        }
        $finalBlocks = [];
        $blockIds = [];
        $blockTpes = [];
        foreach ($page->getBlocks() as $pageblock) {
            if (count($allBlocks) > 0) {
                foreach ($allBlocks as $block) {
                    if ($pageblock->getType() == $block->getType() and $pageblock->getId()==$block->getId() and $pageblock->getType() != 'sonata.page.block.container') {
                        $finalBlocks[] = $block;
                        array_push($blockIds, $block->getId());
                        $blockTpes[] = $block->getType();
                    }
                }
            }
        }
        foreach ($page->getBlocks() as $pageblock) {
            if (count($allBlocks) > 0) {

                foreach ($allBlocks as $block) {
                    if (!in_array($block->getId(), $blockIds) and !in_array($block->getType(), $blockTpes) and $block->getType() != 'sonata.page.block.container') {
                        $finalBlocks[] = $block;
                        array_push($blockIds, $block->getId());
                        array_push($blockTpes, $block->getType());
                    }
                }
                if ((!in_array($pageblock->getId(), $blockIds) and !in_array($pageblock->getType(), $blockTpes)) and $pageblock->getType() != 'sonata.page.block.container') {
                    $finalBlocks[] = $pageblock;
                }
            } else {
                if ($pageblock->getType() != 'sonata.page.block.container') {
                    $finalBlocks[] = $pageblock;
                }

            }
        }
        //dump($finalBlocks);die('call');
        return $this->render('ApplicationSonataPageBundle:preview_templates:' . $object->getTemplateCode() . '.html.twig', ['page' => $object, 'blocks' => $finalBlocks]);


    }

    function unique_multidimensional_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
