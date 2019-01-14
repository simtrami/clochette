<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StocksStockMarket
 *
 * @ORM\Table(name="stocks_stock_market")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StocksRepository")
 */
class StocksStockMarket extends Stocks
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="stock_value", type="decimal", precision=8, scale=2)
     */
    private $stockValue;

    /**
     * @var array
     *
     * @ORM\Column(name="values_history", type="array")
     */
    private $valuesHistory;

    /**
     * @var string
     *
     * @ORM\Column(name="variation", type="decimal", precision=9, scale=6)
     */
    private $variation;

    /**
     * @var string
     *
     * @ORM\Column(name="demand_coefficient", type="decimal", precision=7, scale=6)
     */
    private $demandCoefficient;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return StocksStockMarket
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
     * @return StocksStockMarket
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
     * @return StocksStockMarket
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
     * @return StocksStockMarket
     */
    public function setDemandCoefficient($demandCoefficient)
    {
        $this->demandCoefficient = $demandCoefficient;

        return $this;
    }
}
