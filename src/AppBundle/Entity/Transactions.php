<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transactions
 *
 * @ORM\Table(name="transactions", indexes={
 *     @ORM\Index(name="account", columns={"account"}),
 *     @ORM\Index(name="user", columns={"user"}),
 *     @ORM\Index(name="zreport", columns={"zreport"}),
 *     })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionsRepository")
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
     * @var DateTime
     *
     * @ORM\Column(name="dateTransaction", type="datetime", options={"default" : "2017-12-12 05:40:42"})
     */
    private $timestamp;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account", referencedColumnName="id", nullable=true)
     * })
     */
    private $account;

    /**
     * @var Users
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
     * @ORM\Column(name="methode", type="string", length=7)
     */
    private $methode;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint")
     *
     * @Assert\Range(
     *     min=1,
     *     max=3,
     *     invalidMessage="Type de transaction inconnu"
     * )
     */
    private $type;
  
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\DetailsTransactions", mappedBy="transaction")
     */
    private $details;

    /**
     * @var Zreport
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Zreport", inversedBy="transactions")
     * @ORM\JoinColumn(name="zreport", referencedColumnName="id")
     */
    private $zreport;

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
     * @param datetime $timestamp
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
     * @return DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set account
     *
     * @param Account|null $account
     * @return Transactions
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set user
     *
     * @param Users|null $user
     * @return Transactions
     */
    public function setUser(Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return Users
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
     * Set type
     *
     * @param integer $type
     *
     * @return Transactions
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
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

    /**
     * Set zreport
     *
     * @param Zreport $zreport
     * @return Transactions
     */
    public function setZreport(Zreport $zreport)
    {
        $this->zreport = $zreport;

        return $this;
    }

    /**
     * Get zreport
     *
     * @return Zreport
     */
    public function getZreport()
    {
        return $this->zreport;
    }
}
