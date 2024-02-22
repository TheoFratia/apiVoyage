<?php

namespace App\Controller;

use App\Repository\GeoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
        $jsonQuestions = $cache->get($idcachegetAllCountryAndCity, function(ItemInterface $item) use ($repository, $serializer){
            echo "je suis passé par la";
            $item->tag('Geo');
            $questions = $repository->findAll();
            return $serializer->serialize($questions, 'json', ['groups' => 'getAllCountryAndCity']);;
        });
        
        dd($jsonQuestions);
        return new JsonResponse($jsonQuestions, 200, [], true);
    }
}
