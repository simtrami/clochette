<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stocks
 *
 * @ORM\Table(name="stocks")
 * @ORM\Entity
 */
class Stocks
{
    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=60, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=15, nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantite", type="integer", nullable=false)
     */
    private $quantite;

    /**
     * @var \AppBundle\Entity\Articles
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Articles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idArticle", referencedColumnName="idArticle")
     * })
     */
    private $idarticle;


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
     * @param string $type
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
     * @return string
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
     * Set idarticle
     *
     * @param \AppBundle\Entity\Articles $idarticle
     *
     * @return Stocks
     */
    public function setIdarticle(\AppBundle\Entity\Articles $idarticle)
    {
        $this->idarticle = $idarticle;

        return $this;
    }

    /**
     * Get idarticle
     *
     * @return \AppBundle\Entity\Articles
     */
    public function getIdarticle()
    {
        return $this->idarticle;
    }
}
