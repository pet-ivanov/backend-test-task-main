<?php

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column]
    private ?float $discountAmount = null;

    #[ORM\Column]
    private ?bool $isPercentageDiscount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(float $discountAmount): static
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function isPercentageDiscount(): ?bool
    {
        return $this->isPercentageDiscount;
    }

    public function setIsPercentageDiscount(bool $isPercentageDiscount): static
    {
        $this->isPercentageDiscount = $isPercentageDiscount;

        return $this;
    }

    public function __construct(string $code, float $discountAmount, bool $isPercentageDiscount)
    {
        $this->code = $code;
        $this->discountAmount = $discountAmount;
        $this->isPercentageDiscount = $isPercentageDiscount;
    }

    public function applyDiscount(float $totalPrice): float
    {
        if ($this->isPercentageDiscount) {
            return $totalPrice - ($totalPrice * $this->discountAmount / 100);
        }
        return max($totalPrice - $this->discountAmount, 0);
    }
}
