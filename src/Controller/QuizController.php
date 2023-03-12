<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\ConstraintViolationList;

class QuizController extends AbstractController
{
    #[Route('/api/quiz', name: 'app_quiz', methods: ['POST'])]
    public function createQuiz(
                                #[CurrentUser] User $user,
                               Request $request,
                               ApiRequestValidator $apiRequestValidator,
                                EntityManagerInterface $entityManager,
                              ): JsonResponse
    {
        $dto = $apiRequestValidator->checkRequest($request, Quiz::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $dto->setAuthor($user);
        $entityManager->persist($dto);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz was succesfully created.', 'id' => $dto->getId()], Response::HTTP_CREATED);
    }

    /**
     * This route is meant to create a Route and
     *
     * @param Quiz $quiz
     * @param User $user
     * @param Request $request
     * @param ApiRequestValidator $apiRequestValidator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/quiz/{id}/question', name: 'app_quiz_createquestion', methods: ['POST'])]
    public function createQuestion(
                                    Quiz $quiz,
                                    #[CurrentUser] User $user,
                                    Request $request,
                                    ApiRequestValidator $apiRequestValidator,
                                    EntityManagerInterface $entityManager,
    )
    {
        $dto = $apiRequestValidator->checkRequest($request, Question::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($dto);

        $quiz->addQuestion($dto);
        $entityManager->flush();

        return $this->json($dto, context: [ 'groups' => 'api' ]);
    }
}
