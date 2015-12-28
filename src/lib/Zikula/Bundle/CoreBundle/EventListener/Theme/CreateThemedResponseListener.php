<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener\Theme;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Theme\Engine;
use Zikula_View_Theme;

/**
 * Class CreateThemedResponseListener
 * @package Zikula\Bundle\CoreBundle\EventListener\Theme
 *
 * This class intercepts the Response and modifies it to return a themed Response.
 * It is currently fully BC with Core-1.3 in order to return a smarty-based themed response.
 */
class CreateThemedResponseListener implements EventSubscriberInterface
{
    private $themeEngine;

    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    public function createThemedResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        if (\System::isInstalling()) {
            return;
        }

        $response = $event->getResponse();
        $route = $event->getRequest()->attributes->has('_route') ? $event->getRequest()->attributes->get('_route') : '0'; // default must not be '_'
        if (!($response instanceof Response)
            || is_subclass_of($response, '\Symfony\Component\HttpFoundation\Response')
            || $event->getRequest()->isXmlHttpRequest()
            || false === strpos($response->headers->get('Content-Type'), 'text/html')
            || $route[0] === '_' // the profiler and other symfony routes begin with '_' @todo this is still too permissive
        ) {
            return;
        }

        // all responses are assumed to be themed. PlainResponse will have already returned.
        $twigThemedResponse = $this->themeEngine->wrapResponseInTheme($response);
        if ($twigThemedResponse) {
            $event->setResponse($twigThemedResponse);
        } else {
            // theme is not a twig based theme, revert to smarty
            $smartyThemedResponse = Zikula_View_Theme::getInstance()->themefooter($response);
            $event->setResponse($smartyThemedResponse);
        }
    }

    /**
     * The ThemeEngine::requestAttributes MUST be updated based on EACH Request and not only the initial Request.
     * @param GetResponseEvent $event
     */
    public function setThemeEngineRequestAttributes(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $this->themeEngine->setRequestAttributes($event->getRequest());
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(
                array('setThemeEngineRequestAttributes', 32),
            ),
            KernelEvents::RESPONSE => array(
                array('createThemedResponse')
            ),
        );
    }
}