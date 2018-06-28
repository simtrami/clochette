<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DetailsCommandes
 *
 * @ORM\Table(name="details_commandes",
 *  indexes={
 *      @ORM\Index(name="idCommande", columns={"idCommande"}),
 *      @ORM\Index(name="idArticle", columns={"idArticle"})
 *  }
 * )
 * @ORM\Entity
 */
class DetailsCommandes
{
    /**
     * @var \AppBundle\Entity\Commandes
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Commandes", inversedBy="details")
     * @ORM\JoinColumn(name="idCommande", referencedColumnName="id")
     */
    private $idCommande;

    /**
     * @var \AppBundle\Entity\Stocks
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idArticle", referencedColumnName="id")
     * })
     */
    private $idArticle;

    /**
     * @var integer
     * 
     * @ORM\Column(name="quantite", type="integer")
     */
    private $quantite;

    ## Fonctions

    /**
     * Set idCommande
     *
     * @param \AppBundle\Entity\Commandes $id
     *
     * @return DetailsCommandes
     */
    public function setIdCommande(\AppBundle\Entity\Commandes $idCommande = null)
    {
        $this->idCommande = $idCommande;

        return $this;
    }

    /**
     * Get idCommande
     *
     * @return \AppBundle\Entity\Commandes
     */
    public function getIdCommande()
    {
        return $this->idCommande;
    }
    
    /**
     * Set idArticle
     *
     * @param \AppBundle\Entity\Stocks $id
     *
     * @return DetailsCommandes
     */
    public function setIdArticle(\AppBundle\Entity\Stocks $idArticle = null)
    {
        $this->idArticle = $idArticle;

        return $this;
    }

    /**
     * Get idArticle
     *
     * @return \AppBundle\Entity\Stocks
     */
    public function getIdArticle()
    {
        return $this->idArticle;
    }
    
    /**
     * Set quantite
     *
     * @param integer $quantite
     *
     * @return DetailsCommandes
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
}
