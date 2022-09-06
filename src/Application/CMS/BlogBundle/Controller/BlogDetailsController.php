<?php

namespace App\Application\CMS\BlogBundle\Controller;

use CMS\BaseBundle\Controller\CMSCoreController;
use CMS\FrontUserBundle\Controller\DashboardController;
use CMS\ProductAndServiceBundle\Entity\ProductCustomerReviews;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use CMS\ProductAndServiceBundle\Entity\ProductAndService;
use CMS\ProductAndServiceBundle\Form\Type\ProductAndServicesFormType;
use Symfony\Component\HttpFoundation\Response;
use Traffic\FormBundle\Form\Type\CustomerReviewType;
use Symfony\Component\HttpFoundation\Cookie;


class BlogDetailsController extends CMSCoreController
{
    protected $_data = array();

    public function __construct()
    {
    }

    public function blogDetailAction(Request $request, $slug)
    {
        if (empty($slug)) {
            throw new NotFoundHttpException('404');
        }
        $em = $this->getDoctrine()->getManager();
        $blog = $em->getRepository('Traffic\BlogBundle\Entity\Post')->findOneBy(array(
            'slug' => $slug,
            'enabled' => true
        ));
        $userId = ($this->getUser()) ? $this->getUser()->getId() : '';

        if ($blog) {
            return $this->render('Blogs/blog_details.html.twig', array(
                'blog' => $blog,
            ));
        } else {
            throw new NotFoundHttpException('404');
        }
    }

    private function pageView($request, $productId = null)
    {
        $key = 'tekkie_page_view_' . $productId;
        $cookies = $request->cookies;

        if (empty($cookies)) {
            $response = new Response();
            $cookie = new Cookie($key, $key, time() + (86400 * 1), '/');//1day
            $response->headers->setCookie($cookie);
            $response->send();
            return true;
        } else {
            return false;
        }
    }

    private function getPageViews($value)
    {
        if ($value > 999 && $value <= 999999) {
            $result = floor($value / 1000) . 'K';
        } elseif ($value > 999999) {
            $result = floor($value / 1000000) . 'M';
        } else {
            $result = $value;
        }
        return $result;
    }
}
