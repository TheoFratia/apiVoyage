<?php

namespace App\Controller;

use App\Entity\Geo;
use App\Repository\GeoRepository;
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

class GeoController extends AbstractController
{
    #[Route('/geo', name: 'app_geo')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/GeoController.php',
        ]);
    }

    #[Route('/api/geo', name: 'geo.get', methods: ['GET'])]
    public function getAllCountryAndCityCache(GeoRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllCountryAndCity = "getAllCountryAndCityCache";
        $jsonGeo = $cache->get($idcachegetAllCountryAndCity, function(ItemInterface $item) use ($repository, $serializer){
            echo "je suis passé par la";
            $item->tag('Geo');
            $questions = $repository->findAll();
            return $serializer->serialize($questions, 'json', ['groups' => 'getAllCountryAndCity']);;
        });
        
        dd($jsonGeo);
        return new JsonResponse($jsonGeo, 200, [], true);
    }


    #[Route('/api/geo', name: 'geo.post', methods: ['POST'])]
    public function createPlace(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $geo = $serializer->deserialize($request->getContent(), Geo::class, 'json');
    
        $date = new \DateTime();
        $geo
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setStatus('on');
        
        $errors = $validator->validate($geo);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($geo);
        $manager->flush();
        $cache->invalidateTags(['getAllCountryAndCityCache']);
        $jsonGeo = $serializer->serialize($geo, 'json', ['groups' => 'getAllCountryAndCity']);
        $location = $urlGenerator->generate('geo.get', ['idGeo' => $geo->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonGeo, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }
}
