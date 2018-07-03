<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Comptes
 *
 * @ORM\Table(name="comptes")
 * @ORM\Entity
 */
class Comptes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcompte;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=60)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=60)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="pseudo", type="string", length=60, unique=true)
     */
    private $pseudo;

    /**
     * @var string
     *
     * @ORM\Column(name="solde", type="decimal", precision=8, scale=2, options={"default" : "0.00"})
     */
    private $solde;

    /**
     * @var integer
     *
     * @ORM\Column(name="annee", type="integer", options={"default" : 1})
     */
    private $annee;

    /**
     * @var string
     *
     * @ORM\Column(name="nomStaff", type="string", length=60, nullable=true)
     */
    private $nomstaff;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_intro", type="boolean", options={"default" : false})
     */
    private $isIntro;


    /**
     * Get idcompte
     * 
     * @Groups({"searchable"})
     * 
     * @return integer
     */
    public function getIdcompte()
    {
        return $this->idcompte;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Comptes
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @Groups({"searchable"})
     * 
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Comptes
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @Groups({"searchable"})
     * 
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return Comptes
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get pseudo
     *
     * @Groups({"searchable"})
     * 
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set solde
     *
     * @param string $solde
     *
     * @return Comptes
     */
    public function setSolde($solde)
    {
        $this->solde = $solde;

        return $this;
    }

    /**
     * Get solde
     *
     * @Groups({"searchable"})
     * 
     * @return string
     */
    public function getSolde()
    {
        return $this->solde;
    }

    /**
     * Set annee
     *
     * @param integer $annee
     *
     * @return Comptes
     */
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get annee
     *
     * @Groups({"searchable"})
     * 
     * @return integer
     */
    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    /**
     * Set nomstaff
     *
     * @param string $nomstaff
     *
     * @return Comptes
     */
    public function setNomstaff($nomstaff)
    {
        $this->nomstaff = $nomstaff;

        return $this;
    }

    /**
     * Get nomstaff
     *
     * @Groups({"searchable"})
     * 
     * @return string
     */
    public function getNomstaff()
    {
        return $this->nomstaff;
    }

    /**
     * Set isIntro
     *
     * @param boolean $isIntro
     *
     * @return Comptes
     */
    public function setIsIntro($isIntro)
    {
        $this->isIntro = $isIntro;

        return $this;
    }

    /**
     * Get isIntro
     *
     * @Groups({"searchable"})
     * 
     * @return boolean
     */
    public function getIsIntro()
    {
        return $this->isIntro;
    }
}
