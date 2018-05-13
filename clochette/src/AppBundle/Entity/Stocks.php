<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


 /**
  * 
  * Stocks
 * @ORM\Entity
 * @ORM\Table(name="stocks")
 */

class Stocks
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idarticle", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idarticle;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=60, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prix", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $prix;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=15, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="prixVente", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $prixVente;

    /**
     * @var string
     *
     * @ORM\Column(name="prixAchat", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $prixAchat;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantite", type="integer", nullable=false)
     */
    private $quantite;

    /**
     * @var integer
     *
     * @ORM\Column(name="volume", type="decimal", precision=8, scale = 2, nullable=false)
     */
    private $volume;
    

## Fonctions


    /**
     * Get idarticle
     *
     * @return integer
     */
    public function getIdarticle()
    {
        return $this->idarticle;
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
     * Set prix
     *
     * @param string $prix
     *
     * @return Articles
     */
    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /**
     * Get prix
     *
     * @return string
     */
    public function getPrix()
    {
        return $this->prix;
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
    public function getprixVente()
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
}

    