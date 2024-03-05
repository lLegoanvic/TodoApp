<?php

namespace App\EventSubscribers;


use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Booster;
use App\Entity\Level;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LevelUpdateSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['updateLevelOnXpChange', EventPriorities::PRE_WRITE],
        ];
    }

    /**
     * @throws RandomException
     */
    public function updateLevelOnXpChange(ViewEvent $event): void
    {
        $level = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$level instanceof Level || !in_array($method, ['PUT', 'PATCH'])) {
            return;
        }

        if ($level->getActualXp() > $level->getRequiredXp()) {
            $user = $level->getUserLevel();
            $inventory = $user->getInventory();
            $booster = new Booster();
            $rand = random_int(0,100);
            if($rand<69){
                $booster->setRarity(0);
            }
            if(69 <= $rand && $rand < 94){
                $booster->setRarity(1);
            }
            if(94 <= $rand && $rand < 99){
                $booster->setRarity(2);
            }
            if($rand>= 99){
                $booster->setRarity(3);
            }
            $booster->setInventory($inventory);
            $booster->setCreatedAt(createdAt: new \DateTimeImmutable());
            $this->entityManager->persist($booster);
            $this->entityManager->flush();



            $newLevel = $level->getLevel() + 1;
            $newRequiredXp = (int) ($level->getRequiredXp() * 115/100);


            $level->setLevel($newLevel);
            $level->setActualXp($level->getActualXp() - $level->getRequiredXp());
            $level->setRequiredXp($newRequiredXp);
        }
    }
}