<?php

namespace App\Controller;

use App\Service\ApplicationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api')]
class ApiController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ValidatorInterface $validator
    ) {
    }

    #[Route('/calculate-price', methods: ['POST'])]
    public function calculatePrice(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'product' => new Assert\NotBlank(),
            'taxNumber' => new Assert\NotBlank(),
            'couponCode' => new Assert\Type('string'),
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (\count($errors)) {
            return new JsonResponse(['errors' => iterator_to_array($errors)], 400);
        }

        return new JsonResponse(['message' => 'calculate-price'], 200);
    }

    #[Route('/purchase', methods: ['POST'])]
    public function purchase(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $constraints = new Assert\Collection([
            'product' => new Assert\NotBlank(),
            'taxNumber' => new Assert\NotBlank(),
            'couponCode' => new Assert\Type('string'),
            'paymentProcessor' => new Assert\NotBlank(),
        ]);

        $errors = $this->validator->validate($data, $constraints);
        if (\count($errors)) {
            return new JsonResponse(['errors' => iterator_to_array($errors)], 400);
        }

        return new JsonResponse(['message' => 'purchase'], 200);
    }

}