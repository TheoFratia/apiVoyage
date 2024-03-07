<?php

namespace App\Controller;

use App\Entity\TypeInfo;
use App\Repository\TypeInfoRepository;
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

class TypeInfoController extends AbstractController
{
    #[Route('/type/info', name: 'app_type_info')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/TypeInfoController.php',
        ]);
    }

    #[Route('/api/type/info', name: 'typeinfo.getAll', methods: ['GET'])]
    public function getAllTypeInfoCache(TypeInfoRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllTypeInfo = "getAllTypeInfo";
        $jsonTypeInfo = $cache->get($idcachegetAllTypeInfo, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllTypeInfo');
            $typeInfos = $repository->findAll();
            return $serializer->serialize($typeInfos, 'json', ['groups' => 'getAllTypeInfo']);
        });
        
        return new JsonResponse($jsonTypeInfo, 200, [], true);
    }


    #[Route("/api/type/info/{typeInfo}", name: "typeinfo.get", methods: ["GET"])]
    public function getTypeInfo(TypeInfo $typeInfo, SerializerInterface $serializer): JsonResponse {
        $jsonTypeInfo = $serializer->serialize($typeInfo, 'json', ['groups' => 'getAllTypeInfo']);
        return new JsonResponse($jsonTypeInfo, 200, [], true);
    }



    #[Route('/api/type/info', name: 'typeinfo.post', methods: ['POST'])]
    public function createTypeInfo(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $typeInfo = $serializer->deserialize($request->getContent(), TypeInfo::class, 'json');
    
        $typeInfo
            ->setStatus('on');
        
        $errors = $validator->validate($typeInfo);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($typeInfo);
        $manager->flush();
        $cache->invalidateTags(['getAllTypeInfo']);
        $jsonTypeInfo = $serializer->serialize($typeInfo, 'json', ['groups' => 'getAllTypeInfo']);
        $location = $urlGenerator->generate('typeinfo.getAll', ['idTypeInfo' => $typeInfo->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonTypeInfo, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/type/info/{typeInfo}', name:"typeinfo.update", methods: ['PUT'])]
    public function updateTypeInfo(TypeInfo $typeInfo, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $typeInfo->setStatus('on');
        } else {
            $updateTypeInfoData = $serializer->deserialize($request->getContent(), TypeInfo::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $typeInfo]);
            if (isset($updateTypeInfoData->type)) {
                $typeInfo->setType($updateTypeInfoData->type);
            }
        }

        $cache->invalidateTags(['getAllTypeInfo']);
        $manager->persist($typeInfo);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }



    
    #[Route("/api/type/info/{idTypeInfo}", name:"typeinfo.delete", methods:["DELETE"])]
    public function delete(Request $request, TypeInfo $idTypeInfo, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($idTypeInfo);
        } else {
            $idTypeInfo->setStatus('off');
            $manager->persist($idTypeInfo);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
