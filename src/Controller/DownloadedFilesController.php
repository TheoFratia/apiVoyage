<?php

namespace App\Controller;

use App\Entity\DownloadedFiles;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

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

    #[Route('/api/files', name: 'files.post', methods: ['POST'])]
    public function creatDowloadedFile(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $downloadedFile = new DownloadedFiles();
        $file = $request->files->get('file');
        $downloadedFile->setCreatedAt(new \DateTime());

        $downloadedFile->setFile($file);
        $downloadedFile->setMineType($file->getClientMimeType());
        $downloadedFile->setRealName($file->getClientOriginalName());
        $downloadedFile->setName($file->getClientOriginalName());
        $downloadedFile->setPublicPath("/public/medias/pictures");
        $downloadedFile->setUpdatedAt(new \DateTime());
        $downloadedFile->setCreatedAt(new \DateTime());
        $downloadedFile->setStatus('on');

        $entityManager->persist($downloadedFile);
        $entityManager->flush();

        $jsonFiles = $serializer->serialize($downloadedFile, 'json');
        $location = $urlGenerator->generate('file.get', ["downloadedFile" => $downloadedFile->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonFiles, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    #[Route('/api/files/{downloadedFile}', name: 'file.get', methods: ['Get'])]
    public function getDowloadedFile(DownloadedFiles $downloadedFile, SerializerInterface $serializer, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $pulicPath = $downloadedFile->getPublicPath();
        $location = $urlGenerator->generate('app_downloaded_files', ['id' => $downloadedFile->getId()]);

        $jsonFiles = $serializer->serialize($downloadedFile, 'json');
        $location = $urlGenerator->generate('app_downloaded_files', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $location = $location . str_replace("/public/", " ", $pulicPath.'/'.$downloadedFile->getRealPath());
        $jsonFiles = $serializer->serialize($downloadedFile, 'json');
        return $downloadedFile ?
        new JsonResponse($jsonFiles, Response::HTTP_OK, ['Location' => $location], true) :
        new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
