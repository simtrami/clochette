<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Commandes
 *
 * @ORM\Table(name="commandes", indexes={@ORM\Index(name="compte", columns={"compte"}), @ORM\Index(name="user", columns={"user"})})
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
     *   @ORM\JoinColumn(name="compte", referencedColumnName="id", nullable=true)
     * })
     */
    private $compte;
  
    /**
     * @var \AppBundle\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var string
     * 
     * @ORM\Column(name="montant", type="decimal", precision=8, scale=2)
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
     * Set compte
     *
     * @param \AppBundle\Entity\Comptes $id
     *
     * @return Commandes
     */
    public function setCompte(\AppBundle\Entity\Comptes $compte = null)
    {
        $this->compte = $compte;

        return $this;
    }

    /**
     * Get compte
     *
     * @return \AppBundle\Entity\Comptes
     */
    public function getCompte()
    {
        return $this->compte;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\Users $id
     *
     * @return Commandes
     */
    public function setUser(\AppBundle\Entity\Users $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
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
  
    /**
     * Get details
     * 
     * @return Collection|Details[]
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }
}
