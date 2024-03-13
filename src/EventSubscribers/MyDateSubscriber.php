<?php

namespace App\EventSubscribers;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MyDateSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return
            [
                KernelEvents::VIEW => ['setDate', EventPriorities::PRE_WRITE]];
    }

    public function setDate (ViewEvent $event): void
    {

        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if(Request::METHOD_POST === $method){
            $entity->setCreatedAt(createdAt: new \DateTimeImmutable());
            $entity->setUpdatedAt(updatedAt: new \DateTimeImmutable());
        }elseif (Request::METHOD_PATCH === $method){
            $entity->setUpdatedAt(updatedAt: new \DateTimeImmutable());
        }
    }
}