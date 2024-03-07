<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Repository\EssentialRepository;
use App\Entity\Essential;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class EssentialController extends AbstractController
{
    #[Route('/essential', name: 'app_essential')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EssentialController.php',
        ]);
    }

    #[Route('/api/essential', name: 'essential.getAll', methods: ['GET'])]
    public function getAllCountryAndCityCache(EssentialRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllEssential = "getAllEssential";
        $jsonEssential = $cache->get($idcachegetAllEssential, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllEssential');
            $essentials = $repository->findAll();
            return $serializer->serialize($essentials, 'json', ['groups' => 'getAllEssential']);
        });
        
        dd($jsonEssential);
        return new JsonResponse($jsonEssential, 200, [], true);
    }


    #[Route("/api/essential/{essential}", name: "essential.get", methods: ["GET"])]
    public function getEssential(Essential $essential, SerializerInterface $serializer): JsonResponse {
        $jsonEssential = $serializer->serialize($essential, 'json', ['groups' => 'getAllEssential']);
        return new JsonResponse($jsonEssential, 200, [], true);
    }



    #[Route('/api/essential', name: 'essential.post', methods: ['POST'])]
    public function createEssential(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $essential = $serializer->deserialize($request->getContent(), Essential::class, 'json');
    
        $date = new \DateTime();
        $essential
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setStatus('on');
        
        $errors = $validator->validate($essential);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($essential);
        $manager->flush();
        $cache->invalidateTags(['getAllEssential']);
        $jsonEssential = $serializer->serialize($essential, 'json', ['groups' => 'getAllEssential']);
        $location = $urlGenerator->generate('essential.getAll', ['idEssential' => $essential->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonEssential, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/essential/{essential}', name:"essential.update", methods: ['PUT'])]
    public function updateEssential(Essential $essential, TagAwareCacheInterface $cache, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $essential->setStatus('on');
        } else {
            $updateEssentialData = $serializer->deserialize($request->getContent(), Essential::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $essential]);
            
            if (isset($updateEssentialData->title)) {
                $essential->setTitle($updateEssentialData->title);
            }
            if (isset($updateEssentialData->description)) {
                $essential->setDescription($updateEssentialData->description);
            }

            $date = new \DateTime();
            $essential->setUpdatedAt($date);
        }

        $cache->invalidateTags(['getAllEssential']);
        $manager->persist($essential);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }



    
    #[Route("/api/essential/{idEssential}", name:"essential.delete", methods:["DELETE"])]
    public function delete(Request $request, Essential $idEssential, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ( isset($data['force']) && $data['force'] === true) {
            $manager->remove($idEssential);
        } else {
            $idEssential->setStatus('off');
            $manager->persist($idEssential);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
