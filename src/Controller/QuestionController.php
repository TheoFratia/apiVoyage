<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Doctrine\ORM\Mapping\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class QuestionController extends AbstractController
{
    #[Route('/question', name: 'app_question')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/QuestionController.php',
        ]);
    }

    #[Route('/api/question', name:"question.getAll", methods: ['GET'])]
    public function getAllQuestions(QuestionRepository $repository, TagAwareCacheInterface $cache, SerializerInterface $serializer){
        $idCacheGetAllQuestion = "getAllQuestionCache";
        $jsonQuestions = $cache->get($idCacheGetAllQuestion, function (ItemInterface $item) use ($repository, $serializer) {
            echo "Mise en cache";
            $item->tag("questionCache");
            $questions = $repository->findAll();
            return $serializer->serialize($questions, 'json', ['groups' => 'getAllQuestion']);
        });
        dd($jsonQuestions);

        return new JsonResponse($jsonQuestions, 200, [], true);
    }

    #[Route('/api/question/{idQuestion}', name:"question.get", methods: ['GET'])]
    #[ParamConverter('question', options: ['id' => 'idQuestion'])]
    public function getQuestion(Question $question, SerializerInterface $serializer){
        $jsonQuestions = $serializer->serialize($question, 'json', ['groups' => 'getAllQuestion']);
        return new JsonResponse($jsonQuestions, 200, [], true);
    }

    #[Route('/api/question', name:"question.create", methods: ['POST'])]
    public function createQuestion(Request $request, TagAwareCacheInterface $cache, ValidatorInterface $validator, UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer, EntityManagerInterface $manager){        
        $question = $serializer->deserialize($request->getContent(), Question::class, 'json');
        $date = new \DateTime();
        $question
            ->setCeatedAt($date)
            ->setUpdatedAt($date)
            ->setStatus('on');

        $errors = $validator->validate($question);
        if($errors->count() > 0){
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($question);
        $manager->flush();

        $cache->invalidateTags(["questionCache"]);

        $jsonQuestions = $serializer->serialize($question, 'json', ['groups' => 'getAllQuestion']);
        $location = $urlGenerator->generate('question.get', ['idQuestion' => $question->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        return new JsonResponse($jsonQuestions, JsonResponse::HTTP_CREATED, ["Location" => $location], true);
    }


    #[Route('/api/question/{question}', name:"question.update", methods: ['PUT'])]
    public function updateQuestion(Question $question, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager){
        $updateQuestion = $serializer->deserialize($request->getContent(), Question::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $question]);
        dd($updateQuestion);
    
        $date = new \DateTime();
        $updateQuestion->setUpdatedAt($date);
        $manager->persist($updateQuestion);
        $manager->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


    #[Route('/api/question/{question}', name:"question.delete", methods: ['DELETE'])]
    public function deleteQuestion(Request $request, Question $question, EntityManagerInterface $manager){
        $question->setStatus('off');
        $manager->persist($question);
        //$manager->remove($question);
        $manager->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}