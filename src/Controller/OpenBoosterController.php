<?php

namespace App\Controller;

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


class OpenBoosterController extends AbstractController
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


    /**
     * @throws \JsonException
     */
    #[Route('/api/pkmImg', name: 'pkmImg')]
    public function OpenBooster(Request $request): JsonResponse
    {
        $boosterData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $boosterId = $boosterData['boosterId'];
        $booster = $this->boosterRepository->find($boosterId);

        $token = substr($request->headers->get('Authorization'), 7);
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);

        // extract data from decoded jwt token
        $decodedPayload = json_decode($tokenPayload, true);

        if (!$booster) {
            return new JsonResponse(['error' => 'Booster not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        if($booster->getInventory()->getUserInventory()->getId() !== $decodedPayload['id']){
            return new JsonResponse(['error' => 'Invalid user'], JsonResponse::HTTP_NOT_FOUND);
        }

        for ($i = 0; $i < 5; $i++) {
            $randpkm = random_int(0, 151);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://pokeapi.co/api/v2/pokemon/' . $randpkm,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if ($data) {
                $pkmImgUrl = $data['sprites']['front_default'];
            }else{
                return new JsonResponse(['error' => 'Api pokemon not available'], 400);
            }


            if(!$pkmImgUrl){
                return new JsonResponse(['error' => 'Api pokemon not available'], 400);
            }
            $picture = null;
            $rand = random_int(0, 100);
            if ($booster->getRarity() === 0) {
                if ($rand < 70) {
                    $picture = $this->updateOrCreatePicture('1', $pkmImgUrl, $booster);
                }
                if ($rand >= 70 && $rand < 90) {
                    $picture = $this->updateOrCreatePicture('2', $pkmImgUrl, $booster);
                }
                if ($rand >= 90 && $rand < 99) {
                    $picture = $this->updateOrCreatePicture('3', $pkmImgUrl, $booster);
                }
                if ($rand >= 99) {
                    $picture = $this->updateOrCreatePicture('4', $pkmImgUrl, $booster);
                }
            }
            if ($booster->getRarity() === 1) {
                if ($rand < 40) {
                    $picture = $this->updateOrCreatePicture('1', $pkmImgUrl, $booster);

                }
                if ($rand >= 40 && $rand < 85) {
                    $picture = $this->updateOrCreatePicture('2', $pkmImgUrl, $booster);

                }
                if ($rand >= 85 && $rand < 95) {
                    $picture = $this->updateOrCreatePicture('3', $pkmImgUrl, $booster);

                }
                if ($rand >= 95) {
                    $picture = $this->updateOrCreatePicture('4', $pkmImgUrl, $booster);

                }
            }
            if ($booster->getRarity() === 2) {
                if ($rand < 25) {
                    $picture = $this->updateOrCreatePicture('1', $pkmImgUrl, $booster);

                }
                if ($rand >= 25 && $rand < 70) {
                    $picture = $this->updateOrCreatePicture('2', $pkmImgUrl, $booster);

                }
                if ($rand >= 70 && $rand < 90) {
                    $picture = $this->updateOrCreatePicture('3', $pkmImgUrl, $booster);

                }
                if ($rand >= 90) {
                    $picture = $this->updateOrCreatePicture('4', $pkmImgUrl, $booster);

                }
            }
            if ($booster->getRarity() === 3) {
                if ($rand < 10) {
                    $picture = $this->updateOrCreatePicture('1', $pkmImgUrl, $booster);

                }
                if ($rand >= 10 && $rand < 50) {
                    $picture = $this->updateOrCreatePicture('2', $pkmImgUrl, $booster);

                }
                if ($rand >= 50 && $rand < 80) {
                    $picture = $this->updateOrCreatePicture('3', $pkmImgUrl, $booster);

                }
                if ($rand >= 80) {
                    $picture = $this->updateOrCreatePicture('4', $pkmImgUrl, $booster);

                }
            }


            if($picture !== null ){
                $this->entityManager->persist($picture);
                $this->entityManager->flush();

                $this->entityManager->remove($booster);
                $this->entityManager->flush();
            }else{
                return new JsonResponse(['message'=> 'l\'image n\'existe pas'],404);
            }
        }
        return new JsonResponse(['message' => 'booster correctement ouvert !']);
    }

    public function updateOrCreatePicture($frame, $pkmImgUrl, $booster):Picture
    {
        $picture = $this->pictureRepository->findOneBy(['pkmpicture'=>$pkmImgUrl, 'frame'=>$frame,'inventory'=>$booster->getInventory()]);
        if($picture){
            $quant = $picture->getQuantity();
            $quant ++;
            $picture->setQuantity($quant);
            $picture->setUpdatedAt(new \DateTimeImmutable());
        } else{
            $picture = new Picture();
            $picture->setPkmpicture($pkmImgUrl);
            $picture->setQuantity('1');
            $picture->setFrame($this->frameRepository->find($frame));
            $picture->setCreatedAt(new \DateTimeImmutable());
            $picture->setInventory($booster->getInventory());
        }
        return $picture;
    }
}