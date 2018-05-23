<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TicketZ
 *
 * @ORM\Table(name="TicketZ", indexes={@ORM\Index(name="nom", columns={"nom"}), @ORM\Index(name="idArticle", columns={"idArticle"})})
 * @ORM\Entity
 */
class Commandes
{
    /**
     * @var \AppBundle\Entity\Stocks
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idarticle", referencedColumnName="idarticle")
     * })
     */
    private $idarticle;    
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetenue", type="datetime", options={"default" : "1970-01-01"}, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $datetenue;

    
    /**
     * @var \AppBundle\Entity\Stocks
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="nom", referencedColumnName="nom")
     * })
     */
    private $nom;
   
   
   
   
   
    /**
     * @var string
     *
     * @ORM\Column(name="type de paiement", type="string", length=4, nullable=false)
     */
    private $paytype;




    /**
     * Set idarticle
     *
     * @param \AppBundle\Entity\Stocks $idarticle
     *
     * @return TicketZ
     */
    public function setIdarticle(\AppBundle\Entity\Stocks $idarticle = null)
    {
        $this->idarticle = $idarticle;

        return $this;
    }



    /**
     * Get idarticle
     *
     * @return \AppBundle\Entity\Stocks
     */
    public function getIdarticle()
    {
        return $this->idarticle;
    }


    /**
     * Set nom
     *
     * @param \AppBundle\Entity\Stocks $nom
     *
     * @return TicketZ
     */
    public function setNom(\AppBundle\Entity\Stocks $nom = null)
    {
        $this->nom = $nom;

        return $this;
    }
 
    /**
     * Get datetenue
     *
     * @return \DateTime
     */
    public function getDatetenue()
    {
        return $this->datetenue;
    }

      /**
     * Get $paytype
     *
     * @return TicketZ
     */
    public function getPaytype()
    {
        return $this->paytype;
    }

}
