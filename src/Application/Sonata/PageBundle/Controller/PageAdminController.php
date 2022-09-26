<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Application\Sonata\PageBundle\Controller;

use App\Entity\PagesSlugHistory;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Admin\SnapshotAdmin;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends CRUDController<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
                'sonata.page.admin.snapshot' => SnapshotAdmin::class,
                'sonata.page.admin.block' => BlockAdmin::class,
                'sonata.page.block_interactor' => BlockInteractorInterface::class,
                'sonata.page.manager.site' => SiteManagerInterface::class,
                'sonata.page.manager.page' => PageManagerInterface::class,
                'sonata.page.service.create_snapshot' => CreateSnapshotService::class,
                'sonata.page.site.selector' => SiteSelectorInterface::class,
                'sonata.page.template_manager' => TemplateManagerInterface::class,
                'sonata.block.manager' => BlockServiceManagerInterface::class,
            ] + parent::getSubscribedServices();
    }

    /**
     * @throws AccessDeniedException
     */
    public function batchActionSnapshot(ProxyQueryInterface $query): RedirectResponse
    {
        $this->container->get('sonata.page.admin.snapshot')->checkAccess('create');

        $createSnapshot = $this->container->get('sonata.page.service.create_snapshot');
        foreach ($query->execute() as $page) {
            $createSnapshot->createByPage($page);
        }

        return new RedirectResponse($this->admin->generateUrl('list', [
            'filter' => $this->admin->getFilterParameters(),
        ]));
    }

    public function listAction(Request $request): Response
    {
        if (null === $request->get('filter')) {
            return new RedirectResponse($this->admin->generateUrl('tree'));
        }

        return parent::listAction($request);
    }

    public function treeAction(Request $request): Response
    {
        $this->admin->checkAccess('tree');

        $sites = $this->container->get('sonata.page.manager.site')->findBy([]);
        $pageManager = $this->container->get('sonata.page.manager.page');

        $currentSite = null;
        $siteId = $request->get('site');
        foreach ($sites as $site) {
            if (null !== $siteId && (string)$site->getId() === $siteId) {
                $currentSite = $site;
            } elseif (null === $siteId && $site->getIsDefault()) {
                $currentSite = $site;
            }
        }
        if (!$currentSite && 1 === \count($sites)) {
            $currentSite = $sites[0];
        }

        if ($currentSite) {
            $pages = $pageManager->loadPages($currentSite);
        } else {
            $pages = [];
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();
        $theme = $this->admin->getFilterTheme();
        $this->setFormTheme($formView, $theme);

        return $this->renderWithExtraParams('Application\Sonata\PageBundle\Resources\views\PageAdmin\tree.html.twig', [
            'action' => 'tree',
            'sites' => $sites,
            'currentSite' => $currentSite,
            'pages' => $pages,
            'form' => $formView,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        if ('GET' === $request->getMethod() && null === $request->get('siteId')) {
            $sites = $this->container->get('sonata.page.manager.site')->findBy([]);

            if (1 === \count($sites)) {
                return $this->redirect($this->admin->generateUrl('create', [
                        'siteId' => $sites[0]->getId(),
                        'uniqid' => $this->admin->getUniqId(),
                    ] + $request->query->all()));
            }

            try {
                $current = $this->container->get('sonata.page.site.selector')->retrieve();
            } catch (\RuntimeException $e) {
                $current = false;
            }

            return $this->renderWithExtraParams('@SonataPage/PageAdmin/select_site.html.twig', [
                'sites' => $sites,
                'current' => $current,
            ]);
        }

        return parent::createAction($request);
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeAction(Request $request): Response
    {
        $this->admin->checkAccess('compose');

        $blockAdmin = $this->container->get('sonata.page.admin.block');

        if (false === $blockAdmin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $page = $this->admin->getObject($id);

        if (null === $page) {
            throw new NotFoundHttpException(sprintf('unable to find the page with id : %s', $id));
        }

        $containers = [];
        $orphanContainers = [];
        $children = [];

        $templateManager = $this->container->get('sonata.page.template_manager');
        $template = $templateManager->get($page->getTemplateCode());
        $templateContainers = $template->getContainers();

        foreach ($templateContainers as $containerId => $container) {
            $containers[$containerId] = [
                'area' => $container,
                'block' => false,
            ];
        }

        // 'attach' containers to corresponding template area, otherwise add it to orphans
        foreach ($page->getBlocks() as $block) {
            $blockCode = $block->getSetting('code');
            if (null === $block->getParent()) {
                if (isset($containers[$blockCode])) {
                    $containers[$blockCode]['block'] = $block;
                } else {
                    $orphanContainers[] = $block;
                }
            } else {
                $children[] = $block;
            }
        }

        // searching for block defined in template which are not created
        $blockInteractor = $this->container->get('sonata.page.block_interactor');

        foreach ($containers as $containerId => $container) {
            if (false === $container['block'] && false === $templateContainers[$containerId]['shared']) {
                $blockContainer = $blockInteractor->createNewContainer([
                    'page' => $page,
                    'name' => $templateContainers[$containerId]['name'],
                    'code' => $containerId,
                ]);

                $containers[$containerId]['block'] = $blockContainer;
            }
        }

        return $this->renderWithExtraParams('Application\Sonata\PageBundle\Resources\views\PageAdmin\compose.html.twig', [
            'object' => $page,
            'action' => 'edit',
            'template' => $template,
            'page' => $page,
            'containers' => $containers,
            'orphanContainers' => $orphanContainers,
            'blockAdmin' => $blockAdmin,
            'csrfTokens' => [
                'remove' => $this->getCsrfToken('sonata.delete'),
            ],
        ]);
    }

    /**
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function composeContainerShowAction(Request $request): Response
    {
        $blockAdmin = $this->container->get('sonata.page.admin.block');

        if (false === $blockAdmin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $id = $request->get($this->admin->getIdParameter());
        $block = $blockAdmin->getObject($id);
        if (!$block) {
            throw new NotFoundHttpException(sprintf('unable to find the block with id : %s', $id));
        }

        $blockServices = $this->container->get('sonata.block.manager')->getServicesByContext('sonata_page_bundle', false);

        $page = $block->getPage();

        // filter service using the template configuration
        if (null !== $page) {
            $template = $this->container->get('sonata.page.template_manager')->get($page->getTemplateCode());

            $container = $template->getContainer($block->getSetting('code'));

            if (isset($container['blocks']) && \count($container['blocks']) > 0) {
                foreach ($blockServices as $code => $service) {
                    if (\in_array($code, $container['blocks'], true)) {
                        continue;
                    }

                    unset($blockServices[$code]);
                }
            }
        }

        return $this->renderWithExtraParams('Application\Sonata\PageBundle\Resources\views\PageAdmin\compose_container_show.html.twig', [
            'blockServices' => $blockServices,
            'blockAdmin' => $blockAdmin,
            'container' => $block,
            'page' => $page,
        ]);
    }

    public function cloneAction($id, Request $request): Response
    {

        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->find($id);
        $new_entity = clone $page;
        //$record_lastID = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->findBy(['slug' => $page->getSlug()]);
        $record_lastID = $em->getRepository("App\Application\Sonata\PageBundle\Entity\Page")->createQueryBuilder('p')
           ->where('p.slug LIKE :slug')
           ->setParameter('slug', $page->getSlug().'%')
           ->getQuery()
           ->getResult();

        $LastID = '';
        $url = $page->getUrl();
        if (count($record_lastID) > 0) {
            $LastID = count($record_lastID) + 1;
            $url = $page->getUrl() . '-' . $LastID;
            $slug = $page->getSlug() . '-' . $LastID;
        }
        if ($page->getChangeSlug()) {
            $slug = $page->getSlug();
            if (count($record_lastID) > 0) {
                $LastID = count($record_lastID) + 1;
                $slug = $page->getSlug() . '-' . $LastID;
            }
            $url = '/' . $slug;
            $this->addSlugHistory($page);
        }
        $date = new \DateTime('now');
        $uuid = md5(uniqid($date->format('d-m-Y h:i:s')));
        $new_entity->setUrl($url);
        $new_entity->setSlug($slug);
        $new_entity->setUuid($uuid);
        $new_entity->setParent($page->getParent());
        $new_entity->setCreatedAt(new \DateTime());
        $new_entity->setUpdatedAt(new \DateTime());
        foreach ($page->getBlocks() as $block) {

            if ($block->getType() == 'sonata.page.block.container') {
                $new_blcok = clone $block;
                $new_blcok->setPage($new_entity);
                $em->persist($new_blcok);
                $em->flush($new_blcok);
            }
        }
        foreach ($page->getBlocks() as $block) {

            if ($block->getType() != 'sonata.page.block.container') {
                if (in_array($block->getType(), ['sonata.cms.block.banner_section', 'sonata.cms.block.case_study_banner', 'sonata.cms.block.home_banner'])) {
                    $name = 'Top Content';
                } else {
                    $name = 'Main Content';
                }
                $new_blcok = clone $block;
                $record_lastID = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Block')->findOneBy(array('page' => $new_entity, 'parent' => NULL, 'name' => $name));
                $new_blcok->setPage($new_entity);
                $new_blcok->setParent($record_lastID);
                $em->persist($new_blcok);
                $em->flush($new_blcok);
            }
        }

        $this->addFlash('sonata_flash_success', $new_entity->getName() . ' page duplicated successfully');
        return new RedirectResponse($this->admin->generateUrl(
            'tree',
            array('filter' => $this->admin->getFilterParameters())
        ));
    }

    public function addSlugHistory($object)
    {
        $em = $this->getDoctrine()->getManager();
        $original = $em->getUnitOfWork()->getOriginalEntityData($object);
        $existingHistory = $em->getRepository('App\Entity\PagesSlugHistory')->findOneBy(['page_uuid' => $object->getUuid(), 'slug' => $original['slug']]);
        if (empty($existingHistory)) {
            $slugHistory = new PagesSlugHistory();
            $slugHistory->setSlug($original['slug']);
            $slugHistory->setPageUuid($object->getUuid());
            $em->persist($slugHistory);
            $em->flush($slugHistory);
        }

    }

}
