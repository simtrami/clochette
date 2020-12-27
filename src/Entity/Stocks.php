<?php

namespace App\Entity;

use App\Repository\StocksRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=StocksRepository::class)
 */
class Stocks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40)
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Articles name must be more than {{ limit }} characters.",
     *      maxMessage = "Articles name cannot be more than {{ limit }} characters."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $sellingPrice;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $cost;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $volume;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isForSale = true;

    /**
     * @ORM\ManyToOne(targetEntity=TypeStocks::class, inversedBy="stocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=StockMarketData::class, mappedBy="articleId", cascade={"persist", "remove"})
     */
    private $data;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSellingPrice(): ?string
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(string $sellingPrice): self
    {
        $this->sellingPrice = $sellingPrice;

        return $this;
    }

    public function getCost(): ?string
    {
        return $this->cost;
    }

    public function setCost(string $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVolume(): ?string
    {
        return $this->volume;
    }

    public function setVolume(string $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getIsForSale(): ?bool
    {
        return $this->isForSale;
    }

    public function setIsForSale(bool $isForSale): self
    {
        $this->isForSale = $isForSale;

        return $this;
    }

    public function getType(): ?TypeStocks
    {
        return $this->type;
    }

    public function setType(TypeStocks $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): ?StockMarketData
    {
        return $this->data;
    }

    public function setData(StockMarketData $data): self
    {
        // set the owning side of the relation if necessary
        if ($data->getArticleId() !== $this) {
            $data->setArticleId($this);
        }

        $this->data = $data;

        return $this;
    }
}
