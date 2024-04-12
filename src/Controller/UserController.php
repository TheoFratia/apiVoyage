<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/api/user', name: 'user.post', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, EntityManagerInterface $manager): Response
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setUsername($data['username'])
            ->setPassword($data['password'])
            ->setRoles(["ROLE_USER"])
            ->setUuid(Uuid::uuid4()->toString());

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'error' => true,
                'message' => 'Invalid user data: '. implode(', ', $errorMessages)
            ], 400); // Bad request
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $manager->persist($user);
        $manager->flush();


        return $this->json($user, 201);
    }


    #[Route('/api/user', name: 'api_user', methods: ['GET'])]
    public function fetchUser(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Aucun utilisateur n\'est connecté.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $userData = [
            'id' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ];

        return new JsonResponse($userData);
    }



    #[Route('/api/user/{uuid}', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(User $user): JsonResponse
    {
        $con = $this->security->getUser();
        if (!$con) {
            return new JsonResponse(['message' => 'Aucun utilisateur n\'est connecté.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $userData = [
            'uuid' => $user->getUuid(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'personna'=> $user->getPersonnas(),
        ];

        return $this->json($userData);
    }
}
