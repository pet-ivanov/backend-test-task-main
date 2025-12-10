<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Service\PaymentService;
use App\Repository\CouponRepository;
use App\Service\TaxService;
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
        protected ValidatorInterface $validator,
        protected CouponRepository $couponRepository,
        protected PaymentService $paymentService
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

        try {
            $product = $this->em->find(Product::class, $data['product']);
            if (!$product instanceof Product) {
                return new JsonResponse(['message' => 'Продукт не найден'], 404);
            }

            $countryCode = substr($data['taxNumber'], 0, 2);
            $finalPrice = $product->getPrice();

            if ($data['couponCode']) {
                $coupon = $this->couponRepository->findOneBy(['code' => $data['couponCode']]);
                if ($coupon instanceof Coupon) {
                    $finalPrice = $coupon->applyDiscount($finalPrice);
                }
            }

            $finalPrice += TaxService::calculateTax($finalPrice, $countryCode);

            return new JsonResponse(['final_price' => number_format($finalPrice, 2)]);
        } catch (\Throwable $th) {
            return new JsonResponse(['message' => $th->getMessage()], 500);
        }
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

        try {
            $product = $this->em->find(Product::class, $data['product']);
            if (!$product instanceof Product) {
                return new JsonResponse(['message' => 'Продукт не найден'], 404);
            }

            $countryCode = substr($data['taxNumber'], 0, 2);
            $finalPrice = $product->getPrice();

            if ($data['couponCode']) {
                $coupon = $this->couponRepository->findOneBy(['code' => $data['couponCode']]);
                if ($coupon instanceof Coupon) {
                    $finalPrice = $coupon->applyDiscount($finalPrice);
                }
            }

            $finalPrice += TaxService::calculateTax($finalPrice, $countryCode);

            $result = $this->paymentService->processPayment($finalPrice, $data['paymentProcessor']);
            if ($result === false) {
                return new JsonResponse(['message' => 'Покупка не выполнилась'], 400);
            }

            return new JsonResponse(['message' => 'Покупка успешно совершена'], 200);
        } catch (\Throwable $th) {
            return new JsonResponse(['message' => $th->getMessage()], 500);
        }
    }

}