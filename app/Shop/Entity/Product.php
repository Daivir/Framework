<?php
namespace App\Shop\Entity;

use Virton\Entity\Timestamp;

class Product
{
    /**
     * @var null|int
     */
    private $id;

    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var null|string
     */
    private $slug;

    /**
     * @var null|float
     */
    private $price;

    /**
     * @var null|string
     */
    private $image;

    use Timestamp;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return null|string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param null|string $slug
     */
    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return null|float
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param null|string|float $price
     */
    public function setPrice($price): void
    {
        if (is_string($price)) {
            $price = floatval($price);
        }
        $this->price = $price;
    }

    /**
     * @return null|string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getPdf()
    {
        return "{$this->id}.pdf";
    }

    public function getImageUrl()
    {
        return '/uploads/products/' . $this->getImage();
    }

    public function getThumb()
    {
        ['filename' => $filename, 'extension' => $extension] = pathinfo($this->getImage());
        return '/uploads/products/' . $filename . '_thumb.' . $extension;
    }
}
