<?php
namespace App\Shop\Entity;

class Purchase
{
    /**
     * @var null|int
     */
    private $id;
    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|int
     */
    private $productId;

    /**
     * @var null|float
     */
    private $price;

    /**
     * VAT rate.
     * @var float
     */
    private $vat;

    /**
     * @var null|string
     */
    private $country;

    /**
     * @var null|\DateTime
     */
    private $createdAt;

    /**
     * @var null|string
     */
    private $stripeId;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return null|int
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return null|int
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId(int $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * @return null|float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @return null|float
     */
    public function getVatPrice(): ?float
    {
        return $this->price * $this->vat / 100;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return null|float
     */
    public function getVat(): ?float
    {
        return $this->vat;
    }

    /**
     * @param float $vat
     */
    public function setVat(float $vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return null|string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return null|\DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param null|string|\DateTime $datetime
     * @return void
     */
    public function setCreatedAt($datetime): void
    {
        if (is_string($datetime)) {
            $this->createdAt = new \DateTime($datetime);
        } else {
            $this->createdAt = $datetime;
        }
    }

    /**
     * @return null|string
     */
    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    /**
     * @param string $stripeId
     */
    public function setStripeId(string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }
}
