<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Commandes
 *
 * @ORM\Table(name="commandes", indexes={@ORM\Index(name="idCompte", columns={"idCompte"})})
 * @ORM\Entity
 */
class Commandes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCommande", type="datetime", options={"default" : "2017-12-12 05:40:42"})
     */
    private $timestamp;

    /**
     * @var \AppBundle\Entity\Comptes
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Comptes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idCompte", referencedColumnName="id")
     * })
     */
    private $idCompte;

    /**
     * @var string
     * 
     * @ORM\Column(name="montant", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $montant;
  
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DetailsCommandes", mappedBy="idCommande")
     */
    private $details;

    ## Fonctions

    public function __construct()
    {
      $this->details = new ArrayCollection();
    }
    
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
     * Set timestamp
     *
     * @param \datetime $timestamp
     *
     * @return Commandes
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set idCompte
     *
     * @param \AppBundle\Entity\Comptes $id
     *
     * @return Commandes
     */
    public function setIdCompte(\AppBundle\Entity\Comptes $idCompte = null)
    {
        $this->idCompte = $idCompte;

        return $this;
    }

    /**
     * Get idCompte
     *
     * @return \AppBundle\Entity\Comptes
     */
    public function getIdCompte()
    {
        return $this->idCompte;
    }
    
    /**
     * Set montant
     *
     * @param string $montant
     *
     * @return Commandes
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return string
     */
    public function getMontant()
    {
        return $this->montant;
    }
}
