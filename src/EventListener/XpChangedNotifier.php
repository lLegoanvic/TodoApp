<?php

namespace App\EventListener;


use App\Entity\Booster;
use App\Entity\Level;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;

class XpChangedNotifier
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate(Level $level, PostUpdateEventArgs $event): void
    {
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
            $this->entityManager->persist($level);
            $this->entityManager->flush();
        }
    }

}