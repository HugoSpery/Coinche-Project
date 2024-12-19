<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class QuitGameListener
{

    public function __construct(private HubInterface $hub, private Security $security, private LoggerInterface $logger, private RequestStack $requestStack, private UrlGeneratorInterface $generator)
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        /* $request = $event->getRequest();

         $nextPath = $request->getPathInfo();
         $referer = $request->headers->get('referer');
         $refererPath = parse_url($referer, PHP_URL_PATH);


         if (!str_contains($nextPath,'/game') && str_contains($refererPath,'/party') ){
             $update = new Update(
                 'https://example.com/LeftGame',
                 json_encode($this->security->getUser()->getUsername())
             );
             $this->hub->publish($update);
             $this->logger->info('Update published successfully');
         }*/


       /* $request = $event->getRequest();

        $next = $request->getPathInfo();
        if ("/launch/party" != $next || !str_contains($next,"/party") || !str_contains($next,"/game")){
            if (!$event->isMainRequest()) {
                return;
            }
            $referer = $request->headers->get('referer');
            if ($referer) {
                $urlComponents = parse_url($referer);
                parse_str($urlComponents['query'] ?? '', $previousGetParameters);

                $routeName = $request->attributes->get('_route');
                $currentParameters = $request->query->all();


                if (array_diff_assoc($previousGetParameters, $currentParameters)) {

                    $routeParams = $request->attributes->get('_route_params');
                    $mergedParameters = array_merge($currentParameters, $previousGetParameters, $routeParams);

                    $baseUrl = $this->generator->generate($routeName, $mergedParameters);


                    if ($request->getUri() !== $baseUrl) {
                        $response = new RedirectResponse($baseUrl);
                        $event->setResponse($response);
                    }
                }
            }
        }*/


    }
}
