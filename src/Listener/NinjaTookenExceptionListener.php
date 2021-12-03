<?php

namespace App\Listener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class NinjaTookenExceptionListener{

    protected Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event)
    {

        $exception =  $event->getThrowable();

        $code = 500;
        if (method_exists($exception, 'getStatusCode'))
            $code = $exception->getStatusCode();

        // personnalise notre objet réponse pour afficher les détails de notre exception
        $response = new Response($this->twig->render('exception.html.twig',array(
                'status_code' => $code,
                'status_text' => Response::$statusTexts[$code] ?? '',
                'exception' => $exception,
            ))
        );
        $response->setStatusCode($code);

        // HttpExceptionInterface est un type d'exception spécial qui
        // contient le code statut et les détails de l'entête
        if ($exception instanceof HttpExceptionInterface) {
            $response->headers->replace($exception->getHeaders());
        }

        $response->headers->set('Content-Type', 'text/html');

        // envoie notre objet réponse modifié à l'évènement
        $event->setResponse($response);
    }
}