<?php
namespace App\Cart\Entity;

class Order
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var null|int
     */
    private $userId;

    /**
     * @var null|float
     */
    private $price;

    /**
     * @var null|float
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
     * @var OrderRow[]
     */
    private $rows = [];

    /**
     * @return int
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
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     */
    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return float|null
     */
    public function getVat(): ?float
    {
        return $this->vat;
    }

    /**
     * @param float|null $vat
     */
    public function setVat(?float $vat): void
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
     * @param null|string $country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt ?: new \DateTime;
    }

    /**
     * @param null|string|\DateTime $datetime
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
     * @return string|null
     */
    public function getStripeId(): ?string
    {
        return $this->stripeId;
    }

    /**
     * @param string|null $stripeId
     */
    public function setStripeId(?string $stripeId): void
    {
        $this->stripeId = $stripeId;
    }

    /**
     * @return OrderRow[]
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param OrderRow[] $rows
     */
    public function setRows(array $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * @param OrderRow $row
     */
    public function addRow(OrderRow $row): void
    {
        $this->rows[] = $row;
    }
}
