<?php

namespace App\Entity;

use App\Repository\StockMarketDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StockMarketDataRepository::class)
 */
class StockMarketData
{

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity=Stocks::class, inversedBy="data", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $articleId;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, nullable=true)
     */
    private $stockValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $valuesHistory;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    private $variation;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=6, nullable=true)
     */
    private $demandCoefficient;

    public function getId(): ?int
    {
        return $this->getArticleId()->getId();
    }

    public function getArticleId(): ?Stocks
    {
        return $this->articleId;
    }

    public function setArticleId(Stocks $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }

    public function getStockValue(): ?string
    {
        return $this->stockValue;
    }

    public function setStockValue(?string $stockValue): self
    {
        $this->stockValue = $stockValue;

        return $this;
    }

    public function getValuesHistory(): ?string
    {
        return $this->valuesHistory;
    }

    public function setValuesHistory(?string $valuesHistory): self
    {
        $this->valuesHistory = $valuesHistory;

        return $this;
    }

    public function getVariation(): ?string
    {
        return $this->variation;
    }

    public function setVariation(?string $variation): self
    {
        $this->variation = $variation;

        return $this;
    }

    public function getDemandCoefficient(): ?string
    {
        return $this->demandCoefficient;
    }

    public function setDemandCoefficient(?string $demandCoefficient): self
    {
        $this->demandCoefficient = $demandCoefficient;

        return $this;
    }
}
