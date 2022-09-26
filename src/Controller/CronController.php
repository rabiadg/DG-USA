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

class CronController extends BaseController
{
    public function replicateSite()
    {
        $em = $this->getDoctrineManager();

        $siteCron = $em->getRepository('App\Entity\SiteCron')->findOneBy(['status' => 'In Progress']);
        if (isset($siteCron) and $siteCron != null) {
            if ($siteCron->getModule() == 'page') {
                $pages = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->findBy(['site' => $siteCron->getFromSite(), 'decorate' => 1]);
                $count = 0;
                $array = [];
                $limit = 5;
                $lastId=0;
                if ($siteCron->getRecordsInserted() == 0) {
                    $start = 0;
                } else {
                    $start = $siteCron->getRecordsInserted();
                }
                $query = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')
                    ->createQueryBuilder('p')
                    ->select('p')
                    ->where('p.decorate=1')
                    ->andWhere('p.site= :site');
                $query->setFirstResult($start);
                $query->setMaxResults($limit);
                $result = $query->setParameter("site", $siteCron->getFromSite())
                    //->getQuery()->getSql();
                    ->getQuery()->getResult();

                if (count($result) > 0) {

                    foreach ($result as $r) {
                        $pageExist = $em->getRepository('App\Application\Sonata\PageBundle\Entity\Page')->findOneBy(['url' => $r->getUrl(), 'site' => $siteCron->getToSite()]);
                        if (!$pageExist) {
                            $new_entity = $this->clonePage($r, $siteCron->getToSite());
                            $array['pageIds'][] = $new_entity->getId();
                            $lastId = $r->getId();
                            $count++;
                            $siteCron->setTotalRecords(count($pages));
                        }
                    }
                }

                $siteCron->setRecordsInserted($siteCron->getRecordsInserted() + $count);
                $siteCron->setLastInsertId($lastId);
                if ($siteCron->getRecordsInserted() == $siteCron->getTotalRecords()) {
                    $siteCron->setStatus('Completed');
                }
                $em->persist($siteCron);
                $em->flush();
                return new JsonResponse(['message' => 'success', $array]);
            }
            elseif ($siteCron->getModule() == 'menu') {
                $menus = $em->getRepository('App\Application\CMS\MenuBundle\Entity\CmsMenu')->findBy(['site' => $siteCron->getFromSite()]);
                $count = 0;
                $array = [];
                $limit = 5;
                $lastId=0;
                if ($siteCron->getRecordsInserted() == 0) {
                    $start = 0;
                } else {
                    $start = $siteCron->getRecordsInserted();
                }
                $menuExist = $em->getRepository('App\Application\CMS\MenuBundle\Entity\CmsMenu')->findOneBy(['site' => $siteCron->getToSite()]);
                if (!$menuExist) {
                    foreach ($menus as $menu) {
                        $newMenu = clone $menu;
                        $newMenu->setSite($siteCron->getToSite());
                        $em->persist($newMenu);
                        $em->flush($newMenu);
                        $lastId = $menu->getId();
                        foreach ($newMenu->getItems() as $item) {
                            $newItem = clone $item;
                            $newItem->setMenu($newMenu);
                            if ($newItem->getParent() != null) {
                                $parent = $em->getRepository('App\Application\CMS\MenuBundle\Entity\CmsMenuItems')->findOneBy(['title' => $item->getParent()->getTitle(), 'menu' => $newMenu->getId()]);
                                $newItem->setParent($parent);
                            }
                            $em->persist($newItem);
                            $em->flush($newItem);
                            $array['menuItemIds'][] = $newItem->getId();
                        }
                        $count++;

                    }
                    $siteCron->setTotalRecords(count($menus));
                }
                $siteCron->setRecordsInserted($siteCron->getRecordsInserted() + $count);
                $siteCron->setLastInsertId($lastId);
                if ($siteCron->getRecordsInserted() == $siteCron->getTotalRecords()) {
                    $siteCron->setStatus('Completed');
                    //$siteCron->setAllInserted('Yes');
                }
                $em->persist($siteCron);
                $em->flush();
                return new JsonResponse(['message' => 'success', $array]);

            }
            elseif ($siteCron->getModule() == 'settings') {
                $settings = $em->getRepository('App\Entity\Settings')->findBy(['site' => $siteCron->getFromSite()]);
                $count = 0;
                $array = [];
                $limit = 5;
                $lastId=0;
                if ($siteCron->getRecordsInserted() == 0) {
                    $start = 0;
                } else {
                    $start = $siteCron->getRecordsInserted();
                }
                $settingExist = $em->getRepository('App\Entity\Settings')->findOneBy(['site' => $siteCron->getToSite()]);
                if (!$settingExist) {
                    foreach ($settings as $setting) {
                        $newSetting = clone $setting;
                        $newSetting->setSite($siteCron->getToSite());
                        $em->persist($newSetting);
                        $em->flush($newSetting);
                        $lastId = $setting->getId();
                        $array['settingsIds'][] = $newSetting->getId();
                        $count++;

                    }
                    $siteCron->setTotalRecords(count($settings));
                }

                $siteCron->setRecordsInserted($siteCron->getRecordsInserted() + $count);
                $siteCron->setLastInsertId($lastId);
                if ($siteCron->getRecordsInserted() == $siteCron->getTotalRecords()) {
                    $siteCron->setStatus('Completed');
                }
                $em->persist($siteCron);
                $em->flush();
                return new JsonResponse(['message' => 'success', $array]);

            }
        }

        return new JsonResponse(['message' => 'No Record Found']);
    }
}
