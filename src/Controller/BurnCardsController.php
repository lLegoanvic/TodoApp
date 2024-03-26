<?php

namespace App\Controller;

use ApiPlatform\Exception\ItemNotFoundException;
use App\Entity\Picture;
use App\Repository\BoosterRepository;
use App\Repository\FrameRepository;
use App\Repository\PictureRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;

class BurnCardsController extends AbstractController
{
    public BoosterRepository $boosterRepository;
    public EntityManagerInterface $entityManager;
    public UserRepository $userRepository;
    public FrameRepository $frameRepository;
    public PictureRepository $pictureRepository;

    public function __construct(PictureRepository $pictureRepository, FrameRepository $frameRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, BoosterRepository $boosterRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->boosterRepository = $boosterRepository;
        $this->frameRepository = $frameRepository;
        $this->pictureRepository = $pictureRepository;
    }

    #[Route('/api/fuse', name: 'fuse')]
    public  function fuse(Request $request): JsonResponse
    {
        try{
            $init = $this->init($request);
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $picture = $this->pictureRepository->find($data['id']);
            $decodedPayload = $init['decodedPayload'];
            if(!$picture){
                Throw new ItemNotFoundException('l\'image n\'existe pas.');
            }
            if($picture->getInventory()->getUserInventory()->getId() !== $decodedPayload['id']){
                Throw new \Exception('Invalid user');
            }
            $rarity = $picture->getFrame()->getId();
            $quantity = $picture->getQuantity();
            if($rarity === 1){
                $this->manageRarities($quantity, $picture, 20);
            }
            if($rarity === 2){
                $this->manageRarities($quantity, $picture, 10);
            }
            if($rarity === 3){
                $this->manageRarities($quantity, $picture, 5);
            }
            return new JsonResponse(['message', 'Fusion réalisée !']);
        } catch (\Exception $e){
            return new JsonResponse(['message', $e->getMessage()]);
        }

    }


    #[Route('/api/burn', name: 'burn')]
    public  function burn(Request $request): JsonResponse
    {
        try{
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $init = $this->init($request);
            try{
                $user = $this->userRepository->find($data['id']);
            } catch (\Exception $e){
                return new JsonResponse(['message', $e->getMessage()]);
            }

            $level = $user->getLevel();
            $inventory = $user->getInventory();

            $expGain = 0;
            foreach ($inventory->getPictures() as $picture){
                if($picture->getQuantity()>1){
                    $quantity = $picture->getQuantity()-1;
                    $expGain += ((($picture->getFrame()->getId() ** 2) * 5) * $quantity);
                    $picture->setQuantity(1);
                    $picture->setUpdatedAt(new \DateTimeImmutable());
                    $this->entityManager->persist($picture);
                }

            }
                $newXp = $level->getActualXp() + $expGain;
                $level->setActualXp($newXp);
                $level->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($level);
                $this->entityManager->flush();


            return new JsonResponse(['message', 'cartes burnt avec succès !']);



        } catch (\Exception $e){
            return new JsonResponse(['message', $e->getMessage()]);
        }



    }


    private function init(Request $request): array
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $picture = $this->pictureRepository->find($data['id']);

        $token = substr($request->headers->get('Authorization'), 7);
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $decodedPayload = json_decode($tokenPayload, true);

        return ['decodedPayload' => $decodedPayload];
    }


    private function manageRarities(int $quantity, Picture $picture, int $minus): JsonResponse
    {
        try {
            $betterPicture = $this->pictureRepository->findOneBy(['frame' => $picture->getFrame()->getId() + 1, 'pkmpicture' => $picture->getPkmpicture(), 'inventory' => $picture->getInventory()]);

            if ($quantity > $minus) {

                if ($betterPicture) {
                    $betterPictureNewQuant = $betterPicture->getQuantity() + 1;
                    $betterPicture->setQuantity($betterPictureNewQuant);
                    $pictureNewQuant = $quantity - $minus;
                    $picture->setQuantity($pictureNewQuant);
                    $this->entityManager->persist($betterPicture);
                } else {
                    $newPicture = new Picture();
                    $newPicture->setPkmpicture($picture->getPkmpicture());
                    $newPicture->setQuantity('1');
                    $newPicture->setFrame($this->frameRepository->find($picture->getFrame()->getId() + 1));
                    $newPicture->setCreatedAt(new \DateTimeImmutable());
                    $newPicture->setInventory($picture->getInventory());
                    $newPicture->setPkmId($picture->getPkmId());
                    $newPicture->setPkmName($picture->getPkmName());
                    $pictureNewQuant = $quantity - $minus;
                    $picture->setQuantity($pictureNewQuant);
                    $this->entityManager->persist($newPicture);
                }
                $picture->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($picture);
                $this->entityManager->flush();
            } elseif ($quantity == $minus) {
                if ($betterPicture) {
                    $betterPictureNewQuant = $betterPicture->getQuantity() + 1;
                    $betterPicture->setQuantity($betterPictureNewQuant);
                    $this->entityManager->remove($picture);
                    $this->entityManager->persist($betterPicture);
                } else {
                    $newPicture = new Picture();
                    $newPicture->setPkmpicture($picture->getPkmpicture());
                    $newPicture->setQuantity('1');
                    $newPicture->setFrame($this->frameRepository->find($picture->getFrame()->getId() + 1));
                    $newPicture->setCreatedAt(new \DateTimeImmutable());
                    $newPicture->setInventory($picture->getInventory());
                    $newPicture->setPkmId($picture->getPkmId());
                    $newPicture->setPkmName($picture->getPkmName());
                    $this->entityManager->remove($picture);
                    $this->entityManager->persist($newPicture);
                }
                $this->entityManager->flush();
            }
            return new JsonResponse(['message', 'success']);
        }catch (\Exception $e){
            return new JsonResponse(['message', 'pas assez de carte pour fusionner']);
        }



    }

}