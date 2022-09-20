<?php

namespace App\Controller;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\FrontUser;
use App\Entity\PagesSlugHistory;
use Authy\AuthyResponse;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class BaseController extends AbstractController
{

    public $settingsData;

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        # +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        # User company when user logedin
        //$user = $this->getUser();
        $em = $this->getDoctrine();
        $parameters['HeaderMenu'] = $this->getMenuById(1);
        $parameters['FooterMenu'] = $this->getMenuById(2);
        $parameters['settings'] = $this->getSettings();
        if ($this->getContainer()->has('templating')) {
            $content = $this->getContainer()->get('templating')->render($view, $parameters);
        } elseif ($this->getContainer()->has('twig')) {
            $content = $this->getContainer()->get('twig')->render($view, $parameters);
        } else {
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }

    protected $defaultSite = false;

    public function getContainer()
    {
        return $this->container;
    }


    public function getRequest()
    {
        return $this->getContainer()->get('request_stack')->getCurrentRequest();
    }

    public function getDoctrineManager($manager = 'default')
    {
        return $this->getDoctrine()->getManager($manager);
    }

    public function getSiteByLocale($locale = false)
    {
        $request = $this->getContainer()->get('request_stack')->getCurrentRequest();
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

    public function doctrineNativQuery($sqlQuery)
    {
        $em = $this->getDoctrineManager();
        $stmt = $em->getConnection()->prepare($sqlQuery);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function doctrineNativQueryOneRow($sqlQuery)
    {
        $em = $this->getDoctrineManager();
        $stmt = $em->getConnection()->prepare($sqlQuery);
        $stmt->execute();
        return $stmt->fetchOne();
    }

    public function getMenuById($menuId)
    {
        $mm = $this->getContainer()->get('prodigious_sonata_menu.manager');
        $em = $this->getDoctrineManager();

        $menu = $em->getRepository("App\Application\Sonata\MenuBundle\Entity\Menu")->findOneBy(['id' => $menuId, 'site' => $this->getSiteByLocale()]);


        $em = $this->getDoctrineManager();
        $menuItems = array();
        if ($menu) {
            $menuItems = $em->getRepository("App\Application\Sonata\MenuBundle\Entity\MenuItem")
                ->findBy(array(
                    'menu' => $menu->getId(),
                    'enabled' => 1,
                    'parent' => null
                ), array('position' => 'ASC'));
        }

        //$menuItems = $mm->getMenuItems($menu, true,true);

        return $menuItems;

    }

    public function getMenuByAlias($alias)
    {
        $em = $this->getDoctrineManager();

        $menu = $em->getRepository("App\Application\CMS\MenuBundle\Entity\CmsMenu")->findOneBy(['alias' => $alias, 'site' => $this->getSiteByLocale()]);


        $em = $this->getDoctrineManager();
        $menuItems = array();
        if ($menu) {
            $menuItems = $em->getRepository("App\Application\CMS\MenuBundle\Entity\CmsMenuItems")
                ->findBy(array(
                    'menu' => $menu->getId(),
                    'enabled' => 1,
                    'parent' => null
                ), array('id' => 'ASC'));
        }

        //$menuItems = $mm->getMenuItems($menu, true,true);

        return $menuItems;
    }

    public function getSettings()
    {
        if (!empty($this->settingsData)) {
            return $this->settingsData;
        }
        $setting = $this->getDoctrineManager()
            ->getRepository("App\Entity\Settings")
            ->findBy(array(
                'site' => $this->getSiteByLocale()
            ));
        $data = array();
        foreach ($setting as $s) {
            $data[$s->getSettingsKey()] = $s;
        }

        return $this->settingsData = $data;
    }

    public function deleteEntity($entity)
    {
        $em = $this->getDoctrineManager();
        $qb = $em->createQueryBuilder('s');
        $qb->delete(get_class($entity), 's');
        $qb->where('s.id = :id');
        $qb->setParameter('id', $entity->getId());
        $qb->getQuery()->execute();
    }

    public function doctrineQueryExecute($sqlQuery)
    {
        $em = $this->getDoctrineManager();
        $stmt = $em->getConnection()->prepare($sqlQuery);
        return $stmt->execute();
    }

    public function convertUrlByLocale($url)
    {
        $request = $this->getRequest();
        $new_url = 'javascript:';
        if ($url != '') {
            if (strpos($url, "http") === 0 || strpos($url, "https") === 0) {
                $new_url = $url;
            } else {
                $new_url = ($request->getLocale() != 'en' ? '/' . $request->getLocale() : '') . ($url ?? 'javascript:');
            }
        }
        return $new_url;
    }

    public function translateNumbers($number)
    {
        if ($this->getRequest()->getLocale() == 'ar') {
            return strtr($number, array('0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'));
        } else {
            return $number;
        }

    }

    public function getSites()
    {
        $em = $this->getDoctrineManager();
        return $em->getRepository('App\Application\Sonata\PageBundle\Entity\Site')->findBy(['enabled' => 1]);
    }

    public function setSlug($object)
    {
        $em = $this->getDoctrineManager();
        $slug = $object->getSlug();
        $record = $em->getRepository(get_class($object))->findOneBy(array('slug' => $object->getSlug()));
        if (!empty($record)) {
            $record_lastID = $em->getRepository(get_class($object))->findOneBy(array(), array('id' => 'desc'));
            $LastID = $record_lastID->getId();
            $slug = $object->getSlug(). '-' . ($LastID + 1);
        }
        $object->setSlug($slug);
    }

    public function addSlugHistory($object)
    {
        $em = $this->getDoctrineManager();
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

    public function getQueryByClass($class, $limit = null, $where = array(),$isSite=false, $orderBy = null)
    {
        $DM = $this->getDoctrineManager();
        $query = $DM->getRepository($class)
            ->createQueryBuilder('a')
            ->select('a')
            ->where("a.enabled=1");
        if($isSite){
            $query->andWhere('a.site= :site');
        }


        if (isset($orderBy) && isset($orderBy['column'])) {
            $query->orderBy('a.' . $orderBy['column'] . '', $orderBy['orderBy']);
        } else {
            $query->orderBy("a.id", " DESC");
        }

        if (!empty($limit)) {
            $query->setMaxResults($limit);
        }
        if($isSite){
            $query->setParameter("site", $this->getSiteByLocale());
        }
        $result =
            $query->getQuery()
            ->getResult();
        return $result;
    }


}