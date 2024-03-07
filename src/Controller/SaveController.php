<?php

namespace App\Controller;

use App\Entity\Save;
use App\Repository\SaveRepository;
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
    public function getAllCountryAndCityCache(SaveRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllSave = "getAllSave";
        $jsonSave = $cache->get($idcachegetAllSave, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllSave');
            $saves = $repository->findAll();
            return $serializer->serialize($saves, 'json', ['groups' => 'getAllSave']);
        });
        
        dd($jsonSave);
        return new JsonResponse($jsonSave, 200, [], true);
    }

    #[Route("/api/save/{save}", name: "save.get", methods: ["GET"])]
    public function getSave(Save $save, SerializerInterface $serializer): JsonResponse {
        $jsonSave = $serializer->serialize($save, 'json', ['groups' => 'getAllSave']);
        return new JsonResponse($jsonSave, 200, [], true);
    }



    #[Route('/api/save', name: 'save.post', methods: ['POST'])]
    public function createSave(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $save = $serializer->deserialize($request->getContent(), Save::class, 'json');
    
        $date = new \DateTime();
        $save
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setStatus('on');
        
        $errors = $validator->validate($save);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($save);
        $manager->flush();
        $cache->invalidateTags(['getAllSave']);
        $jsonSave = $serializer->serialize($save, 'json', ['groups' => 'getAllSave']);
        $location = $urlGenerator->generate('save.getAll', ['idSave' => $save->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonSave, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/save/{save}', name:"save.update", methods: ['PUT'])]
    public function updateSave(Save $save, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $save->setStatus('on');
        } else {
            $updateSaveData = $serializer->deserialize($request->getContent(), Save::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $save]);
        }

        $cache->invalidateTags(['getAllSave']);
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
