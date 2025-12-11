<?php

namespace Tests\App\Controller;

use App\Controller\ApiController;
use App\Entity\Product;
use App\Entity\Coupon;
use App\Repository\CouponRepository;
use App\Service\PaymentService;
use App\Service\TaxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use PHPUnit\Framework\MockObject\Matcher\AnyParameters;

class ApiControllerTest extends KernelTestCase
{
    protected EntityManagerInterface $emMock;
    protected ValidatorInterface $validatorMock;
    protected CouponRepository $couponRepositoryMock;
    protected PaymentService $paymentServiceMock;
    protected TaxService $taxServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->emMock = $this->createMock(EntityManagerInterface::class);
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->couponRepositoryMock = $this->createMock(CouponRepository::class);
        $this->paymentServiceMock = $this->createMock(PaymentService::class);
        $this->taxServiceMock = $this->createMock(TaxService::class);
    }

    public function testCalculatePriceSuccess(): void
    {
        $controller = new ApiController($this->emMock, $this->validatorMock, $this->couponRepositoryMock, $this->paymentServiceMock);

        $product = new Product();
        $product->setPrice(100);

        $this->emMock->method('find')->willReturn($product);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->couponRepositoryMock->method('findOneBy')->willReturn(null); // Нет активного купона
        $this->taxServiceMock->method('calculateTax')
            ->with(any())
            ->willReturn(19); // Налог 19%

        $request = new Request([], [], [], [], [], [], '{"product":1,"taxNumber":"DE123456789"}');

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['final_price' => '119.00']), $response->getContent());
    }

    public function testCalculatePriceWithCoupon(): void
    {
        $controller = new ApiController($this->emMock, $this->validatorMock, $this->couponRepositoryMock, $this->paymentServiceMock);

        $product = new Product();
        $product->setPrice(100);

        $coupon = new Coupon('DISCOUNT10', 10, true); // Код, скидка (%), применяется как %

        $this->emMock->method('find')->willReturn($product);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());
        $this->couponRepositoryMock->method('findOneBy')->willReturn($coupon);
        $this->taxServiceMock->method('calculateTax')
            ->with(any())
            ->willReturn(19);

        $request = new Request([], [], [], [], [], [], '{"product":1,"taxNumber":"DE123456789","couponCode":"DISCOUNT10"}');

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['final_price' => '108.00']), $response->getContent()); // Цена после скидки и налога
    }

    public function testCalculatePriceValidationErrors(): void
    {
        $controller = new ApiController($this->emMock, $this->validatorMock, $this->couponRepositoryMock, $this->paymentServiceMock);

        $violation = new \Symfony\Component\Validator\ConstraintViolation(
            'This value should not be blank.',
            '{{ value }} is required.',
            [],
            null,
            'product',
            ''
        );

        $violationList = new ConstraintViolationList([$violation]); // Список с нашим нарушением

        $this->validatorMock->method('validate')->willReturn($violationList);

        $request = new Request([], [], [], [], [], [], '{}'); // Некорректный запрос

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testCalculatePriceProductNotFound(): void
    {
        $controller = new ApiController($this->emMock, $this->validatorMock, $this->couponRepositoryMock, $this->paymentServiceMock);

        $this->emMock->method('find')->willReturn(null);
        $this->validatorMock->method('validate')->willReturn(new ConstraintViolationList());

        $request = new Request([], [], [], [], [], [], '{"product":9999,"taxNumber":"DE123456789"}');

        $response = $controller->calculatePrice($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Продукт не найден']), $response->getContent());
    }
}