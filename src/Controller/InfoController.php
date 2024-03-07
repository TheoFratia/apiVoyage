<?php

namespace App\Controller;

use App\Entity\Info;
use App\Repository\InfoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class InfoController extends AbstractController
{
    #[Route('/info', name: 'app_info')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/InfoController.php',
        ]);
    }

    #[Route('/api/info', name: 'info.getAll', methods: ['GET'])]
    public function getAllCountryAndCityCache(InfoRepository $repository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idcachegetAllInfo = "getAllInfo";
        $jsonInfo = $cache->get($idcachegetAllInfo, function(ItemInterface $item) use ($repository, $serializer){
            $item->tag('getAllInfo');
            $infos = $repository->findAll();
            return $serializer->serialize($infos, 'json', ['groups' => 'getAllInfo']);
        });
        
        dd($jsonInfo);
        return new JsonResponse($jsonInfo, 200, [], true);
    }


    #[Route("/api/info/{info}", name: "info.get", methods: ["GET"])]
    public function getInfo(Info $info, SerializerInterface $serializer): JsonResponse {
        $jsonInfo = $serializer->serialize($info, 'json', ['groups' => 'getAllInfo']);
        return new JsonResponse($jsonInfo, 200, [], true);
    }



    #[Route('/api/info', name: 'info.post', methods: ['POST'])]
    public function createInfo(Request $request, TagAwareCacheInterface $cache, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $info = $serializer->deserialize($request->getContent(), Info::class, 'json');
    
        $date = new \DateTime();
        $info
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setStatus('on');
        
        $errors = $validator->validate($info);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($info);
        $manager->flush();
        $cache->invalidateTags(['getAllInfo']);
        $jsonInfo = $serializer->serialize($info, 'json', ['groups' => 'getAllInfo']);
        $location = $urlGenerator->generate('info.getAll', ['idInfo' => $info->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonInfo, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/info/{info}', name:"info.update", methods: ['PUT'])]
    public function updateInfo(Info $info, Request $request, SerializerInterface $serializer, TagAwareCacheInterface $cache, EntityManagerInterface $manager): JsonResponse
    {
        $active = json_decode($request->getContent(), true);

        if (isset($active['active']) && $active['active'] === true) {
            $info->setStatus('on');
        } else {
            $updateInfoData = $serializer->deserialize($request->getContent(), Info::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $info]);

            if (isset($updateInfoData->description)) {
                $info->setDescription($updateInfoData->description);
            }

            if (isset($updateInfoData->idTypeInfo)) {
                $info->setIdTypeInfo($updateInfoData->idTypeInfo);
            }

            $date = new \DateTime();
            $info->setUpdatedAt($date);
        }

        $cache->invalidateTags(['getAllInfo']);
        $manager->persist($info);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }



    
    #[Route("/api/info/{idInfo}", name:"info.delete", methods:["DELETE"])]
    public function delete(Request $request, Info $idInfo, EntityManagerInterface $manager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['force']) && $data['force'] === true) {
            $manager->remove($idInfo);
        } else {
            $idInfo->setStatus('off');
            $manager->persist($idInfo);
        }
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
