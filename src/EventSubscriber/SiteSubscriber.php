<?php

// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteSubscriber implements EventSubscriberInterface
{
    private $defaultSite;

    public function __construct(string $defaultSite = '1')
    {
        $this->defaultSite = $defaultSite;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        //dump($request->query->get('locale'));die('call');
        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($site = $request->query->get('_site')) {
            $request->getSession()->set('_site', $site);
        } elseif(empty($request->getSession()->get('_site'))) {
            // if no explicit locale has been set on this request, use one from the session
            $request->getSession()->set('_site', $this->defaultSite);
            //$request->setLocale($request->getSession()->get('_site', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}