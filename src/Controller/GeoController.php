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
        $item->tag('getAllCountryAndCityCache');
        $geos = $repository->findAll();
        $uniqueCountries = [];
        $uniqueCities = [];

        foreach ($geos as $geo) {
            // Vérifier si le pays est déjà présent dans le tableau uniqueCountries
            if (!in_array($geo->getCountry(), $uniqueCountries)) {
                $uniqueCountries[] = $geo->getCountry();
            }

            // Vérifier si la ville est déjà présente dans le tableau uniqueCities
            if (!in_array($geo->getCity(), $uniqueCities)) {
                $uniqueCities[] = $geo->getCity();
            }
        }

        // Construction du tableau de résultat
        $result = [
            'countries' => $uniqueCountries,
            'cities' => $uniqueCities
        ];

        return $serializer->serialize($result, 'json', ['groups' => 'getAllCountryAndCity']);
    });
    
    return new JsonResponse($jsonGeo, 200, [], true);
}



    #[Route("/api/geo/{geo}", name: "geo.getBy", methods: ["GET"])]
    public function getByCity(string $geo, GeoRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse {
            $theCity = $repository->findByCity($geo);
            $theCountry = $repository->findByCountry($geo);
            if (empty($theCity) && empty($theCountry)) {
                return new JsonResponse('Aucun resultat pour "' . $geo . '"', 404);
            } elseif (empty($theCountry)) {
                $geos = $theCity;
            } else {
                $geos = $theCountry;
            }
            return new JsonResponse($serializer->serialize($geos, 'json', ['groups' => 'getByCityOrCountry']), 200, [], true);
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


    #[Route("/api/geo/{geo}/{budget}", name: "geo.getActivitiesByBudget", methods: ["GET"])]
    public function getActivitiesByBudget(string $geo, float $budget, GeoRepository $geoRepository, SerializerInterface $serializer): JsonResponse
    {
        // Récupérer les informations de la ville ou du pays
        $theCity = $geoRepository->findByCity($geo);
        $theCountry = $geoRepository->findByCountry($geo);
    
        if (empty($theCity) && empty($theCountry)) {
            return new JsonResponse('Aucun résultat pour "' . $geo . '"', 404);
        }
    
        $geoEntities = !empty($theCity) ? $theCity : $theCountry;
        $result = [];
    
        foreach ($geoEntities as $geoEntity) {
            $activities = $geoEntity->getPointOfInterests();
            $filteredActivities = [];
            $otherPointsOfInterest = [];
    
            foreach ($activities as $activity) {
                $isActivity = false;
                foreach ($activity->getIdIType() as $type) {
                    if ($type->getType() === 'activity') {
                        $isActivity = true;
                        break;
                    }
                }
                if ($isActivity) {
                    $filteredActivities[] = $activity;
                } else {
                    $otherPointsOfInterest[] = $activity;
                }
            }

            usort($filteredActivities, function ($a, $b) {
                return $b->getNote() <=> $a->getNote();
            });

            $selectedActivities = [];
            $totalCost = 0;
    
            foreach ($filteredActivities as $activity) {
                $activityCost = $activity->getPrice();
                if ($totalCost + $activityCost <= $budget) {
                    $selectedActivities[] = $activity;
                    $totalCost += $activityCost;
                }
            }
    
            // Préparer les résultats pour inclure les informations géographiques
            $geoInfo = [
                'id' => $geoEntity->getId(),
                'city' => $geoEntity->getCity(),
                'country' => $geoEntity->getCountry(),
                'address' => $geoEntity->getAddress(),
                'longitude' => $geoEntity->getLongitude(),
                'latitude' => $geoEntity->getLatitude(),
                'updated_at' => $geoEntity->getUpdatedAt()->format('c'),
                'created_at' => $geoEntity->getCreatedAt()->format('c'),
                'status' => $geoEntity->getStatus(),
                'zipCode' => $geoEntity->getZipCode(),
                'pointOfInterests' => []
            ];
    
            foreach ($selectedActivities as $activity) {
                $geoInfo['pointOfInterests'][] = [
                    'id' => $activity->getId(),
                    'description' => $activity->getDescription(),
                    'link' => $activity->getLink(),
                    'price' => $activity->getPrice(),
                    'updated_at' => $activity->getUpdatedAt()->format('c'),
                    'created_at' => $activity->getCreatedAt()->format('c'),
                    'idIType' => array_map(function($type) { return ['type' => $type->getType()]; }, $activity->getIdIType()->toArray()),
                    'status' => $activity->getStatus(),
                    'titre' => $activity->getTitre(),
                    'imageLink' => $activity->getImageLink(),
                    'Note' => $activity->getNote(),
                ];
            }
    
            // Ajouter les autres points d'intérêt sans filtrage par budget
            foreach ($otherPointsOfInterest as $activity) {
                $geoInfo['pointOfInterests'][] = [
                    'id' => $activity->getId(),
                    'description' => $activity->getDescription(),
                    'link' => $activity->getLink(),
                    'price' => $activity->getPrice(),
                    'updated_at' => $activity->getUpdatedAt()->format('c'),
                    'created_at' => $activity->getCreatedAt()->format('c'),
                    'idIType' => array_map(function($type) { return ['type' => $type->getType()]; }, $activity->getIdIType()->toArray()),
                    'status' => $activity->getStatus(),
                    'titre' => $activity->getTitre(),
                    'imageLink' => $activity->getImageLink(),
                    'Note' => $activity->getNote(),
                ];
            }
    
            $result[] = $geoInfo;
        }
    
        if (empty($result)) {
            return new JsonResponse('Aucune activité trouvée pour le budget spécifié.', 404);
        }
    
        return new JsonResponse($serializer->serialize($result, 'json', ['groups' => 'getByCityOrCountry']), 200, [], true);
    }




    #[Route('/api/geo/{geo}', name:"geo.update", methods: ['PUT'])]
    public function updateGeo(Geo $geo, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager) {
        
        $requestData = json_decode($request->getContent(), true);
        if (isset($requestData['active']) && $requestData['active'] === true) {
            $geo->setStatus('on');
        } else {
            // Désérialiser les données JSON pour obtenir les valeurs à mettre à jour
            $updateGeoData = $serializer->deserialize($request->getContent(), Geo::class, 'json');
            
            // Vérifier si la propriété est définie dans les données JSON avant de la modifier
            if (isset($requestData['city'])) {
                $geo->setCity($updateGeoData->getCity());
            }
            if (isset($requestData['country'])) {
                $geo->setCountry($updateGeoData->getCountry());
            }
            if (isset($requestData['address'])) {
                $geo->setAddress($updateGeoData->getAddress());
            }
            if (isset($requestData['status'])) {
                $geo->setStatus($updateGeoData->getStatus());
            }
            if (isset($requestData['latitude'])) {
                $geo->setLatitude($updateGeoData->getLatitude());
            }
            if (isset($requestData['longitude'])) {
                $geo->setLongitude($updateGeoData->getLongitude());
            }
            if (isset($requestData['zip_code'])) {
                $geo->setUpdatedAt($updateGeoData->getZipCode());
            }
            
            $date = new \DateTime();
            $geo->setUpdatedAt($date);
        }
    
        $manager->persist($geo);
        $manager->flush();
    
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
    
    


    #[Route("/api/geo/{idGeo}", name:"geo.delete", methods:["DELETE"])]
    public function delete(Request $request, Geo $idGeo, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($idGeo);
        } else {
            $idGeo->setStatus('off');
            $manager->persist($idGeo);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
