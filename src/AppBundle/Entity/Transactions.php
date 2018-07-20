<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transactions
 *
 * @ORM\Table(name="transactions", indexes={@ORM\Index(name="compte", columns={"compte"}), @ORM\Index(name="user", columns={"user"})})
 * @ORM\Entity
 */
class Transactions
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
     * @ORM\Column(name="dateTransaction", type="datetime", options={"default" : "2017-12-12 05:40:42"})
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
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * @var string
     * 
     * @ORM\Column(name="montant", type="decimal", precision=8, scale=2)
     * @Assert\GreaterThan(0)
     */
    private $montant;

    /**
     * @var string
     *
     * @ORM\Column(name="methode", type="string", length=10)
     */
    private $methode;
  
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DetailsTransactions", mappedBy="transaction")
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
     * @return Transactions
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
     * @return Transactions
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
     * @return Transactions
     */
    public function setUser(\AppBundle\Entity\Users $user = null)
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
     * @return Transactions
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
     * Set methode
     *
     * @param string $methode
     *
     * @return Transactions
     */
    public function setMethode($methode)
    {
        $this->methode = $methode;

        return $this;
    }

    /**
     * Get methode
     *
     * @return string
     */
    public function getMethode()
    {
        return $this->methode;
    }
  
    /**
     * Get details
     * 
     * @return ArrayCollection
     */
    public function getDetails()
    {
        return $this->details;
    }
}
