<?php

namespace App\Controller;

use App\Entity\Save;
use App\Entity\User;
use App\Entity\PointOfInterest;
use App\Repository\SaveRepository;
use App\Repository\PointOfInterestRepository;
use App\Repository\GeoRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


class SaveController extends AbstractController
{
    #[Route('/save', name: 'app_save')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SaveController.php',
        ]);
    }

    #[Route('/api/save', name: 'save.getAll', methods: ['GET'])]
    public function getAllSave(SaveRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $saves = $repository->findAll();
        return new JsonResponse($jsonSave, 200, [], true);
    }

    #[Route('/api/saves/{uuid}/{saveName}', name: 'saves_by_uuid_name', methods: ['GET'])]
    public function getSavesByUuidAndName(string $uuid, string $saveName, SaveRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $saves = $repository->findByUserUuidAndSaveName($uuid, $saveName);

        $jsonContent = $serializer->serialize($saves, 'json', ['groups' => ['getAllSave', 'excludeGeo']]);
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('api/saves/{uuid}', name: 'get_saves_by_user')]
    public function getSavesByUser(string $uuid, UserRepository $repositoryU, SaveRepository $saveRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $repositoryU->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $saves = $saveRepository->findBy(['UserId' => $user]);

        // Array to hold merged saves by name
        $mergedSaves = [];

        foreach ($saves as $save) {
            $saveName = $save->getName();

            if (!isset($mergedSaves[$saveName])) {
                $mergedSaves[$saveName] = $save;
            } else {
                // Merge PointOfInterest
                foreach ($save->getIdPointOfInterest() as $pointOfInterest) {
                    $mergedSaves[$saveName]->addIdPointOfInterest($pointOfInterest);
                }
            }
        }

        // Calculate total price of all PointOfInterest
        $totalPrice = 0;

        foreach ($mergedSaves as $save) {
            foreach ($save->getIdPointOfInterest() as $pointOfInterest) {
                $totalPrice += $pointOfInterest->getPrice();
            }
        }

        $response = [];
        foreach ($mergedSaves as $save) {
            $serializedSave = $serializer->serialize($save, 'json', ['groups' => ['getAllSave']]);
            $decodedSave = json_decode($serializedSave, true);
            $decodedSave['totalPrice'] = $totalPrice;
            $response[] = $decodedSave;
        }

        return new JsonResponse($response);
    }

    #[Route('/api/save', name: 'save.post', methods: ['POST'])]
    public function createSave(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $manager, 
        ValidatorInterface $validator,
        UrlGeneratorInterface $urlGenerator,
        PointOfInterestRepository $repositoryP,
        UserRepository $repositoryU,
        GeoRepository $repositoryG): JsonResponse {

        $data = json_decode($request->getContent(), true);
        $save = new Save();
        foreach ($data["idPointOfInterest"] as $pointOfInterest) {

            $newPointOfInterest = $repositoryP->find($pointOfInterest);
            $save->addIdPointOfInterest($newPointOfInterest);
        }

        $newUser = $repositoryU->findOneByUuid($data["uuid"]);
        if (!$newUser) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }
        $save->setName($data["name"]);
        $save->setUserId($newUser);
        $save->setStatus('on');

        // Valider l'entité
        $errors = $validator->validate($save);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        // Persister l'entité principale
        $manager->persist($save);
        $manager->flush();

        // Sérialiser la réponse
        $jsonSave = $serializer->serialize($save, 'json', ['groups' => 'getAllSave']);
        $location = $urlGenerator->generate('save.getAll', ['idSave' => $save->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);

        return new JsonResponse($jsonSave, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }


    #[Route('/api/save/{save}', name:"save.update", methods: ['PUT'])]
    public function updateSave(Save $save, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager, PointOfInterestRepository $repositoryP, UserRepository $repositoryU, GeoRepository $repositoryG, ValidatorInterface $validator,): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $save->setStatus('on');
        } else {
            $data = json_decode($request->getContent(), true);

            if(isset($data["idPointOfInterest"]))
            {
                foreach ($data["idPointOfInterest"] as $pointOfInterest) {

                    $newPointOfInterest = $repositoryP->find($pointOfInterest);
                    $save->addIdPointOfInterest($newPointOfInterest);
                }
            }
            if (isset($data["idGeo"]))
            {
                $newGeo = $repositoryG->find($data["idGeo"]);
                $save->setIdGeo($newGeo);
            }
            if (isset($data["UserId"]))
            {
                $newUser = $repositoryU->find($data["UserId"]);
                $save->setUserId($newUser);
            }

            // Valider l'entité
            $errors = $validator->validate($save);
            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
        
        }

        $manager->persist($save);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route("/api/save", name: "save.delete", methods: ["DELETE"])]
    public function delete(Request $request, SaveRepository $saveRepository, EntityManagerInterface $manager, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifiez que les paramètres nécessaires sont présents
        if (!isset($data['idPointOfInterest']) || !isset($data['uuid'])) {
            return new JsonResponse(['error' => 'idPointOfInterest and uuid must be provided'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Utilisez la méthode du repository pour supprimer l'entité Save
        try {
            $user = $userRepository->findOneBy(['uuid' => $data['uuid']]);
            $saveRepository->deleteByPointOfInterestAndUser($data['idPointOfInterest'], $user->getId());

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Delete operation failed: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
