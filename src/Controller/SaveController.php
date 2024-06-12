<?php

namespace App\Controller;

use App\Entity\Save;
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

    #[Route('/api/user/{userId}/geo/{geoId}', name: 'get_by_user_and_geo', methods: ['GET'])]
    public function getByUserAndGeo(int $userId, int $geoId, SaveRepository $saveRepository, SerializerInterface $serializer): JsonResponse
    {
        $saves = $saveRepository->findByUserIdAndGeoId($userId, $geoId);
        $jsonSaves = $serializer->serialize($saves, 'json', ['groups' => 'getAllSave']);

        return new JsonResponse($jsonSaves, JsonResponse::HTTP_OK, [], true);
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

        $newGeo = $repositoryG->find($data["idGeo"]);
        $save->setIdGeo($newGeo);
        $newUser = $repositoryU->find($data["UserId"]);
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

    
    #[Route("/api/save/{idSave}", name:"save.delete", methods:["DELETE"])]
    public function delete(Request $request, Save $idSave, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($idSave);
        } else {
            $idSave->setStatus('off');
            $manager->persist($idSave);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
