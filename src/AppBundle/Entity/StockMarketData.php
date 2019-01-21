<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StocksStockMarket
 *
 * @ORM\Table(name="stock_market_data")
 * @ORM\Entity()
 */
class StockMarketData
{
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Stocks", inversedBy="data")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     * @ORM\Id
     */
    private $articleId;

    /**
     * @var string
     *
     * @ORM\Column(name="stock_value", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $stockValue;

    /**
     * @var array
     *
     * @ORM\Column(name="values_history", type="array", nullable=true)
     */
    private $valuesHistory;

    /**
     * @var string
     *
     * @ORM\Column(name="variation", type="decimal", precision=9, scale=6, nullable=true)
     */
    private $variation;

    /**
     * @var string
     *
     * @ORM\Column(name="demand_coefficient", type="decimal", precision=7, scale=6, nullable=true)
     */
    private $demandCoefficient;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->articleId;
    }

    /**
     * Get article.
     *
     * @return Stocks
     */
    public function getArticle()
    {
        return $this->articleId;
    }

    /**
     * Set articleId.
     *
     * @param Stocks $article
     *
     * @return StockMarketData
     */
    public function setArticle($article)
    {
        $this->articleId = $article;

        return $this;
    }

    /**
     * Get stockValue.
     *
     * @return string
     */
    public function getStockValue()
    {
        return $this->stockValue;
    }

    /**
     * Set stockValue.
     *
     * @param string $stockValue
     *
     * @return StockMarketData
     */
    public function setStockValue($stockValue)
    {
        $this->stockValue = $stockValue;

        return $this;
    }

    /**
     * Get valuesHistory.
     *
     * @return array
     */
    public function getValuesHistory()
    {
        return $this->valuesHistory;
    }

    /**
     * Set valuesHistory.
     *
     * @param array $valuesHistory
     *
     * @return StockMarketData
     */
    public function setValuesHistory($valuesHistory)
    {
        $this->valuesHistory = $valuesHistory;

        return $this;
    }

    /**
     * Get variation.
     *
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * Set variation.
     *
     * @param string $variation
     *
     * @return StockMarketData
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;

        return $this;
    }

    /**
     * Get demandCoefficient.
     *
     * @return string
     */
    public function getDemandCoefficient()
    {
        return $this->demandCoefficient;
    }

    /**
     * Set demandCoefficient.
     *
     * @param string $demandCoefficient
     *
     * @return StockMarketData
     */
    public function setDemandCoefficient($demandCoefficient)
    {
        $this->demandCoefficient = $demandCoefficient;

        return $this;
    }
}
