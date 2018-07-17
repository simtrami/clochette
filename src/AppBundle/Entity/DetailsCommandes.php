<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DetailsCommandes
 *
 * @ORM\Table(name="details_commandes",
 *  indexes={
 *      @ORM\Index(name="commande", columns={"commande"}),
 *      @ORM\Index(name="article", columns={"article"})
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
     * @ORM\JoinColumn(name="commande", referencedColumnName="id")
     */
    private $commande;
    
    /**
     * @var \AppBundle\Entity\Stocks
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="article", referencedColumnName="id")
     * })
     */
    private $article;

    /**
     * @var integer
     * 
     * @ORM\Column(name="quantite", type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $quantite;

    ## Fonctions

    /**
     * Set commande
     *
     * @param \AppBundle\Entity\Commandes $id
     *
     * @return DetailsCommandes
     */
    public function setCommande(\AppBundle\Entity\Commandes $commande)
    {
        $this->commande = $commande;

        return $this;
    }

    /**
     * Get commande
     *
     * @return \AppBundle\Entity\Commandes
     */
    public function getCommande()
    {
        return $this->commande;
    }
    
    /**
     * Set article
     *
     * @param \AppBundle\Entity\Stocks $id
     *
     * @return DetailsCommandes
     */
    public function setArticle(\AppBundle\Entity\Stocks $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \AppBundle\Entity\Stocks
     */
    public function getArticle()
    {
        return $this->article;
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
