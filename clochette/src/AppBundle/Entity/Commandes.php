<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commandes
 *
 * @ORM\Table(name="commandes", indexes={@ORM\Index(name="idCompte", columns={"idCompte"}), @ORM\Index(name="idArticle", columns={"idArticle"})})
 * @ORM\Entity
 */
class Commandes
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateCommande", type="datetime", options={"default" : "1970-01-01"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $datecommande;

    /**
     * @var \AppBundle\Entity\Articles
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Articles")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idArticle", referencedColumnName="idArticle")
     * })
     */
    private $idarticle;

    /**
     * @var \AppBundle\Entity\Comptes
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Comptes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idCompte", referencedColumnName="idCompte")
     * })
     */
    private $idcompte;


    /**
     * Get datecommande
     *
     * @return \DateTime
     */
    public function getDatecommande()
    {
        return $this->datecommande;
    }

    /**
     * Set idarticle
     *
     * @param \AppBundle\Entity\Articles $idarticle
     *
     * @return Commandes
     */
    public function setIdarticle(\AppBundle\Entity\Articles $idarticle = null)
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

    /**
     * Set idcompte
     *
     * @param \AppBundle\Entity\Comptes $idcompte
     *
     * @return Commandes
     */
    public function setIdcompte(\AppBundle\Entity\Comptes $idcompte = null)
    {
        $this->idcompte = $idcompte;

        return $this;
    }

    /**
     * Get idcompte
     *
     * @return \AppBundle\Entity\Comptes
     */
    public function getIdcompte()
    {
        return $this->idcompte;
    }
}
