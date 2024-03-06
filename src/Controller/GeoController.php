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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
            $geos = $repository->findAll();
            return $serializer->serialize($geos, 'json', ['groups' => 'getAllCountryAndCity']);;
        });
        
        dd($jsonGeo);
        return new JsonResponse($jsonGeo, 200, [], true);
    }


    #[Route("/api/geo/{geo}", name: "geo.getBy", methods: ["GET"])]
    public function getByCity(string $geo, GeoRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse {
        $cacheId = 'getBy';
        $jsonGeo = $cache->get($cacheId, function (ItemInterface $item) use ($geo, $repository, $serializer) {
            $item->tag('getBy');
            $theCity = $repository->findByCity($geo);
            $theCountry = $repository->findByCountry($geo);
            if (empty($theCity) && empty($theCountry)) {
                return null;
            } elseif (empty($theCountry)) {
                $geos = $theCity;
            } else {
                $geos = $theCountry;
            }
            return $serializer->serialize($geos, 'json', ['groups' => 'getByCityOrCountry']);
        });

        if (is_null($jsonGeo)) {
            return new JsonResponse('Aucun resultat pour "' . $geo . '"', 404);
        }

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
        $cache->invalidateTags(['getAllCountryAndCityCache', 'getBy']);
        $jsonGeo = $serializer->serialize($geo, 'json', ['groups' => 'getByCityOrCountry']);
        $location = $urlGenerator->generate('geo.get', ['idGeo' => $geo->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonGeo, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/geo/{geo}', name:"geo.update", methods: ['PUT'])]
    public function updateGeo(Geo $geo, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager) {
        $updateGeoData = $serializer->deserialize($request->getContent(), Geo::class, 'json');
        if ($updateGeoData['active'] === true) {
            $geo->setStatus('on');
        }else {
            $geo->setCity($updateGeoData->getCity());
            $geo->setCountry($updateGeoData->getCountry());
            $geo->setAddress($updateGeoData->getAddress());
            $geo->setStatus($updateGeoData->getStatus());
            $geo->setLatitude($updateGeoData->getLatitude());
            $geo->setLongitude($updateGeoData->getLongitude());
        
            $date = new \DateTime();
            $geo->setUpdatedAt($date);
        }

        $manager->persist($geo);
        $manager->flush();
    
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    #[Route('/api/geo/{geo}', name:"geo.update", methods: ['PUT'])]
    public function activeGeo(Geo $geo, EntityManagerInterface $manager) {
        $geo->setStatus("on");

        $manager->persist($geo);
        $manager->flush();
    
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    

    
    #[Route("/api/geo/{idGeo}", name:"geo.delete", methods:["DELETE"])]
    public function delete(Request $request, Geo $idGeo, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data['force'] === true) {
            $manager->remove($idGeo);
        } else {
            $idGeo->setStatus('off');
            $manager->persist($idGeo);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
