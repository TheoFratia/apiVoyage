<?php

namespace App\Controller;

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
use App\Repository\TypePointOfInterestRepository;
use App\Entity\TypePointOfInterest;

class TypePointOfInterestController extends AbstractController
{
    #[Route('/type/pointofinterest', name: 'app_type_pointofinterest')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TypePointOfInterestController.php',
        ]);
    }

    #[Route('/api/type/pointofinterest', name: 'typepointofinterest.getAll', methods: ['GET'])]
    public function getAllCountryAndCityCache(TypePointOfInterestRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllTypePointOfInterest = "getAllTypePointOfInterest";
        $jsonTypePointOfInterest = $cache->get($idcachegetAllTypePointOfInterest, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllTypePointOfInterest');
            $typePointOfInterests = $repository->findAll();
            return $serializer->serialize($typePointOfInterests, 'json', ['groups' => 'getAllTypePointOfInterest']);
        });
        
        dd($jsonTypePointOfInterest);
        return new JsonResponse($jsonTypePointOfInterest, 200, [], true);
    }


    #[Route("/api/type/pointofinterest/{typePointOfInterest}", name: "typepointofinterest.get", methods: ["GET"])]
    public function getTypePointOfInterest(TypePointOfInterest $typePointOfInterest, SerializerInterface $serializer): JsonResponse {
        $jsonTypePointOfInterest = $serializer->serialize($typePointOfInterest, 'json', ['groups' => 'getAllTypePointOfInterest']);
        return new JsonResponse($jsonTypePointOfInterest, 200, [], true);
    }



    #[Route('/api/type/pointofinterest', name: 'typepointofinterest.post', methods: ['POST'])]
    public function createTypePointOfInterest(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $typePointOfInterest = $serializer->deserialize($request->getContent(), TypePointOfInterest::class, 'json');
    
        $typePointOfInterest
            ->setStatus('on');
        
        $errors = $validator->validate($typePointOfInterest);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($typePointOfInterest);
        $manager->flush();
        $cache->invalidateTags(['getAllTypePointOfInterest']);
        $jsonTypePointOfInterest = $serializer->serialize($typePointOfInterest, 'json', ['groups' => 'getAllTypePointOfInterest']);
        $location = $urlGenerator->generate('typepointofinterest.getAll', ['idTypePointOfInterest' => $typePointOfInterest->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonTypePointOfInterest, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/type/pointofinterest/{typePointOfInterest}', name:"typepointofinterest.update", methods: ['PUT'])]
    public function updateTypePointOfInterest(TypePointOfInterest $typePointOfInterest, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $typePointOfInterest->setStatus('on');
        } else {
            $updateTypePointOfInterestData = $serializer->deserialize($request->getContent(), TypePointOfInterest::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $typePointOfInterest]);
            if (isset($updateTypePointOfInterestData->type)) {
                $typePointOfInterest->setType($updateTypePointOfInterestData->type);
            }
        }

        $cache->invalidateTags(['getAllTypePointOfInterest']);
        $manager->persist($typePointOfInterest);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }



    
    #[Route("/api/type/pointofinterest/{idTypePointOfInterest}", name:"typepointofinterest.delete", methods:["DELETE"])]
    public function delete(Request $request, TypePointOfInterest $idTypePointOfInterest, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($idTypePointOfInterest);
        } else {
            $idTypePointOfInterest->setStatus('off');
            $manager->persist($idTypePointOfInterest);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
