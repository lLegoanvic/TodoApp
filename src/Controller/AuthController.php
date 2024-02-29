<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;

class AuthController extends AbstractController
{
    public EntityManagerInterface $entityManager;
    public UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/api/register', name: 'apiRegister')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Invalid email format'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $user = new User();
        $user->setEmail($email);

        $password = $data['password'];
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            return new JsonResponse(['message' => 'Password must be at least 8 characters long and contain at least one lowercase letter, one uppercase letter, one digit, and one special character'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setPassword($userPasswordHasher->hashPassword($user, $password));

        $username = $data['username'];
        if($this->getUser($username)!== null){
            return new JsonResponse(['message' => 'username already in use, choose another one'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user->setUsername($username);
        $user->setRoles('ROLE_USER');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'User registered!'], JsonResponse::HTTP_CREATED);

    }

}