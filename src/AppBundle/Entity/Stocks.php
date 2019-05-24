<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Stocks
 *
 * @ORM\Table(name="stocks")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StocksRepository")
 */

class Stocks
{

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TypeStocks")
     * @ORM\JoinColumn()
     */
    private $type;


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=40, nullable=false)
     * @Assert\Length(
     *      min = 2,
     *      max = 40,
     *      minMessage = "Le nom de l'article doit faire au moins {{ limit }} caractères",
     *      maxMessage = "Le nom de l'article ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $nom;


    /**
     * @var string
     *
     * @ORM\Column(name="prixVente", type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $prixVente;

    /**
     * @var string
     *
     * @ORM\Column(name="prixAchat", type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $prixAchat;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantite", type="integer", nullable=false)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $quantite;

    /**
     * @var string
     *
     * @ORM\Column(name="volume", type="decimal", precision=8, scale=2, nullable=true)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $volume;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="isForSale", type="boolean", nullable=true, options={"default" : false})
     */
    private $isForSale = true;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\StockMarketData", mappedBy="articleId")
     */
    private $data;
    

## Fonctions


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Stocks
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set type
     *
     * @param TypeStocks $type
     *
     * @return Stocks
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return TypeStocks
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set quantite
     *
     * @param integer $quantite
     *
     * @return Stocks
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Get quantite
     *
     * @return integer
     */
    public function getQuantite()
    {
        return $this->quantite;
    }

    /**
     * Set prixAchat
     *
     * @param string $prixAchat
     *
     * @return Stocks
     */
    public function setPrixAchat($prixAchat)
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }

    /**
     * Get prixAchat
     *
     * @return string
     */
    public function getPrixAchat()
    {
        return $this->prixAchat;
    }


    /**
     * Set prixVente
     *
     * @param string $prixVente
     *
     * @return Stocks
     */
    public function setPrixVente($prixVente)
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    /**
     * Get prixVente
     *
     * @return string
     */
    public function getPrixVente()
    {
        return $this->prixVente;
    }

    /**
     * Set volume
     *
     * @param string $volume
     *
     * @return Stocks
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return string
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set isForSale
     *
     * @param boolean $isForSale
     *
     * @return Stocks
     */
    public function setIsForSale($isForSale)
    {
        $this->isForSale = $isForSale;

        return $this;
    }

    /**
     * Get isForSale
     *
     * @return boolean
     */
    public function getIsForSale()
    {
        return $this->isForSale;
    }

    /**
     * Set data
     *
     * @param StockMarketData $data
     *
     * @return Stocks
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return StockMarketData
     */
    public function getData()
    {
        return $this->data;
    }
}
