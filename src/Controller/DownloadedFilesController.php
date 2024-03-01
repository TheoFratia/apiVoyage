<?php

namespace App\Controller;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\DownloadedFiles;

class DownloadedFilesController extends AbstractController
{
    #[Route('/', name: 'app_downloaded_files')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DownloadedFilesController.php',
        ]);
    }

    #[Route('/api/files/{downloadedFiles}', name: 'file.get', methods: ['GET'])]
    public function getDownloadedFile(DownloadedFiles $downloadedFiles, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $publicPath = $downloadedFiles->getPublicPath();
        $location = $urlGenerator->generate('app_downloaded_files', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = $location . str_replace("/public/", "", $publicPath ."/".$downloadedFiles->getPublicPath());
        $jsonFiles = $serializer->serialize($downloadedFiles, 'json');

        return $downloadedFiles ?
        new JsonResponse($jsonFiles, JsonResponse::HTTP_OK,["Location" => $location], true) :
        new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
    }

    #[Route('/api/files', name: 'app_downloaded_files', methods: ['POST'])]
    public function createDownloadedFile(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse
    {
        $downloadFile = new DownloadedFiles();
        $file = $request->files->get('file');

        $downloadFile->setFile($file);
        $downloadFile->setMineType($file->getClientMimeType());
        $downloadFile->setRealName($file->getClientOriginalName());
        $downloadFile->setName($file->getClientOriginalName());
        $downloadFile->setPublicPath("/public/medias/pictures");
        $downloadFile->setUpdatedAt(new \DateTime());
        $downloadFile->setCreatedAt(new \DateTime());
        $downloadFile->setStatus("on");

        $entityManager->persist($downloadFile);
        $entityManager->flush();

        $jsonFiles = $serializer->serialize($downloadFile, 'json');
        $location = $urlGenerator->generate('file.get', ["downloadedFiles" => $downloadFile->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonFiles, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }
}
