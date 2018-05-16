<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="idCompte", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcompte;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=60, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=60, nullable=false)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=60, nullable=false, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=10, nullable=false, unique=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="solde", type="decimal", precision=8, scale=2, nullable=false)
     */
    private $solde = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="annee", type="integer", nullable=false)
     */
    private $annee = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="nomStaff", type="string", length=60, nullable=true)
     */
    private $nomstaff;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_intro", type="boolean", nullable=false)
     */
    private $isIntro = '0';


    /**
     * Get idcompte
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
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set login
     *
     * @param string $login
     *
     * @return Comptes
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
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
     * @return integer
     */
    public function getAnnee()
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
     * @return boolean
     */
    public function getIsIntro()
    {
        return $this->isIntro;
    }
}