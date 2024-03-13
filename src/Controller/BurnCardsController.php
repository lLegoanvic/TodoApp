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
            $picture = $init['picture'];
            $rarity = $init['rarity'];
            $quantity = $init['quantity'];
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
            $quantToBurn = $data['quantToBurn'];
            $init = $this->init($request);
            $picture = $init['picture'];
            $rarity = $init['rarity'];
            $quantity = $init['quantity'];
            $expGain = ($rarity ** 2) * 5;
            $user = $picture->getInventory()->getUserInventory();
            $level = $user->getLevel();
            if($quantToBurn>$quantity){
                return new JsonResponse(['error', 'pas assez de cartes à burn.']);
            }
            for ($i=0; $i<$quantToBurn+1; $i++){
                if($quantity === 1){
                    $this->entityManager->remove($picture);
                } else{
                    $newQuant = $quantity --;
                    $picture->setQuantity($newQuant);
                    $picture->setUpdatedAt(new \DateTimeImmutable());
                    $this->entityManager->persist($picture);
                }

                $newXp = $level->getActualXp() + $expGain;
                $level->setActualXp($newXp);
                $level->setUpdatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($level);
                $this->entityManager->flush();

            }
            return new JsonResponse(['message', 'cartes burnt avec succès !']);



        } catch (\Exception $e){
            return new JsonResponse(['message', $e->getMessage()]);
        }



    }


    private function init(Request $request): JsonResponse|array
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $picture = $this->pictureRepository->find($data['id']);

        $token = substr($request->headers->get('Authorization'), 7);
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $decodedPayload = json_decode($tokenPayload, true);
        if(!$picture){
            Throw new ItemNotFoundException('l\'image n\'existe pas.');
        }
        if($picture->getInventory()->getUserInventory()->getId() !== $decodedPayload['id']){
            Throw new \Exception('Invalid user');
        }

        $rarity = $picture->getFrame()->getId();
        $quantity = $picture->getQuantity();
        return ['picture' => $picture, 'quantity' => $quantity, 'rarity' => $rarity];
    }


    private function manageRarities(int $quantity, Picture $picture, int $minus): JsonResponse
    {
        if ($quantity > $minus) {
            $betterPicture = $this->pictureRepository->findOneBy(['frame' => $picture->getFrame()->getId() + 1, 'pkmpicture'=>$picture->getPkmpicture(), 'inventory'=>$picture->getInventory()]);
            if ($betterPicture) {
                $betterPictureNewQuant = $betterPicture->getQuantity() + 1;
                $betterPicture->setQuantity($betterPictureNewQuant);
                $pictureNewQuant = $quantity - $minus;
                $picture->setQuantity($pictureNewQuant);
                $this->entityManager->persist($betterPicture);
            } else{
                $newPicture = new Picture();
                $newPicture->setPkmpicture($picture->getPkmpicture());
                $newPicture->setQuantity('1');
                $newPicture->setFrame($this->frameRepository->find($picture->getFrame()->getId() + 1));
                $newPicture->setCreatedAt(new \DateTimeImmutable());
                $newPicture->setInventory($picture->getInventory());
                $pictureNewQuant = $quantity - $minus;
                $picture->setQuantity($pictureNewQuant);
                $this->entityManager->persist($newPicture);
            }
            $picture->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($picture);
            $this->entityManager->flush();
        }

        Throw new \Exception('pas assez de cartes à fusionner.');

    }

}