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
use App\Repository\PointOfInterestRepository;
use App\Entity\PointOfInterest;

class PointofinterestController extends AbstractController
{
    #[Route('/pointofinterest', name: 'app_pointofinterest')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PointofinterestController.php',
        ]);
    }

    #[Route('/api/pointofinterest', name: 'pointofinterest.getAll', methods: ['GET'])]
    public function getAllCountryAndCityCache(PointOfInterestRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllPointOfInterest = "getAllPointOfInterest";
        $jsonPointOfInterest = $cache->get($idcachegetAllPointOfInterest, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllPointOfInterest');
            $PointOfInterests = $repository->findAll();
            return $serializer->serialize($PointOfInterests, 'json', ['groups' => 'getAllPointOfInterest']);
        });
        
        dd($jsonPointOfInterest);
        return new JsonResponse($jsonPointOfInterest, 200, [], true);
    }


    #[Route("/api/pointofinterest/{pointOfInterest}", name: "pointofinterest.get", methods: ["GET"])]
    public function getPointOfInterest(PointOfInterest $pointOfInterest, SerializerInterface $serializer): JsonResponse {
        $jsonPointOfInterest = $serializer->serialize($pointOfInterest, 'json', ['groups' => 'getAllPointOfInterest']);
        return new JsonResponse($jsonPointOfInterest, 200, [], true);
    }



    #[Route('/api/pointofinterest', name: 'pointofinterest.post', methods: ['POST'])]
    public function createPointOfInterest(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $PointOfInterest = $serializer->deserialize($request->getContent(), PointOfInterest::class, 'json');
    
        $PointOfInterest
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setStatus('on');
        
        $errors = $validator->validate($PointOfInterest);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($PointOfInterest);
        $manager->flush();
        $cache->invalidateTags(['getAllPointOfInterest']);
        $jsonPointOfInterest = $serializer->serialize($PointOfInterest, 'json', ['groups' => 'getAllPointOfInterest']);
        $location = $urlGenerator->generate('pointofinterest.getAll', ['idPointOfInterest' => $PointOfInterest->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonPointOfInterest, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/pointofinterest/{PointOfInterest}', name:"pointofinterest.update", methods: ['PUT'])]
    public function updatePointOfInterest(PointOfInterest $PointOfInterest, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $PointOfInterest->setStatus('on');
        } else {
            $updatePointOfInterestData = $serializer->deserialize($request->getContent(), PointOfInterest::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $PointOfInterest]);
            if (isset($updatePointOfInterestData->description)) {
                $PointOfInterest->setDescription($updatePointOfInterestData->description);
            }
            if (isset($updatePointOfInterestData->link)) {
                $PointOfInterest->setLink($updatePointOfInterestData->link);
            }
            if (isset($updatePointOfInterestData->price)) {
                $PointOfInterest->setPrice($updatePointOfInterestData->price);
            }
            $PointOfInterest->setUpdatedAt(new \DateTime());
        }

        $cache->invalidateTags(['getAllPointOfInterest']);
        $manager->persist($PointOfInterest);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    
    #[Route("/api/pointofinterest/{PointOfInterest}", name:"pointofinterest.delete", methods:["DELETE"])]
    public function delete(Request $request, PointOfInterest $PointOfInterest, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($PointOfInterest);
        } else {
            $PointOfInterest->setStatus('off');
            $manager->persist($PointOfInterest);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
