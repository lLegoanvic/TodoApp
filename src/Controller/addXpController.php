<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class addXpController extends AbstractController
{
    public UserRepository $userRepository;
    public EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }


    #[Route('/api/addXp', name: 'addXp')]
    public function addXp(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $xpToAdd = $data['xp'];
        $token = substr($request->headers->get('Authorization'), 7);
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $decodedPayload = json_decode($tokenPayload, true);
        $user = $this->userRepository->find($decodedPayload['id']);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $level = $user->getLevel();
        $newXp = $xpToAdd + $level->getActualXp();
        $level->setActualXp($newXp);
        $level->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($level);
        $this->entityManager->flush();
        return new JsonResponse(['message', 'xp bien ajoutée']);

    }
}