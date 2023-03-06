<?php

namespace App\Service;

use App\Entity\Quiz;
use Lcobucci\JWT\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiRequestValidator
{
    public SerializerInterface $serializer;
    public ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer,  ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function checkRequest(Request $request, string $format): mixed
    {
        try {
            $dto = $this->serializer->deserialize($request->getContent(), $format, 'json', [
                DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
            ]);

            $errors = $this->validator->validate($dto);

            if (count($errors) > 0) {
                /*
                 * Uses a __toString method on the $errors variable which is a
                 * ConstraintViolationList object. This gives us a nice string
                 * for debugging.
                 */

                return $errors;
            }

            return $dto;
        } catch (PartialDenormalizationException $e) {
            $violations = new ConstraintViolationList();

            foreach ($e->getErrors() as $exception) {
                $message = sprintf('The type must be one of "%s" ("%s" given).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
                $parameters = [];
                if ($exception->canUseMessageForUser()) {
                    $parameters['hint'] = $exception->getMessage();
                }

                $violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
            };

            return $violations;
        }
    }
}