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

    #[Route('/api/register', name: 'apiregister')]
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

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): JsonResponse
    {
        // Récupérer les données de la requête JSON
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Récupérer l'email et le mot de passe de la requête
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Vérifier si l'email et le mot de passe sont présents
        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Tentative de connexion de l'utilisateur
        try {
            $user = $this->userRepository->find($email);

            // Vérifier si l'utilisateur existe et si le mot de passe est correct
            if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
                throw new BadCredentialsException('Invalid credentials');
            }

            // Générer un token JWT pour l'utilisateur
            $token = $this->jwtEncoder->encode([
                'email' => $user->getEmail(),
                // Ajouter d'autres données de l'utilisateur au token si nécessaire
            ]);

            // Retourner le token JWT en réponse
            return new JsonResponse(['token' => $token], JsonResponse::HTTP_OK);
        } catch (BadCredentialsException $exception) {
            return new JsonResponse(['error' => 'Invalid email or password'], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (\Exception $exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}