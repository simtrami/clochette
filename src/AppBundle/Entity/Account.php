<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Account
 *
 * @ORM\Table(name="account")
 * @ORM\Entity
 */
class Account
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
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=30)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Votre nom doit faire au moins {{ limit }} caractères",
     *      maxMessage = "Votre nom ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=30)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Votre prénom doit faire au moins {{ limit }} caractères",
     *      maxMessage = "Votre prénom ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="pseudo", type="string", length=30, unique=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Votre pseudo doit faire au moins {{ limit }} caractères",
     *      maxMessage = "Votre pseudo ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $pseudo;

    /**
     * @var string
     *
     * @ORM\Column(name="balance", type="decimal", precision=8, scale=2, options={"default" : "0.00"})
     */
    private $balance;

    /**
     * @var integer
     *
     * @ORM\Column(name="year", type="integer", options={"default" : 1})
     * @Assert\GreaterThanOrEqual(1)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="staff_name", type="string", length=30, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "Le nom de staff doit faire au moins {{ limit }} caractères",
     *      maxMessage = "Le nom de staff ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $staffName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_inducted", type="boolean", options={"default" : false})
     */
    private $isInducted;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Transactions", mappedBy="account")
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @Groups({"searchable"})
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Account
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @Groups({"searchable"})
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Account
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @Groups({"searchable"})
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set pseudo
     *
     * @param string $pseudo
     *
     * @return Account
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

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
     * Set balance
     *
     * @param string $balance
     *
     * @return Account
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get balance
     *
     * @Groups({"searchable"})
     *
     * @return string
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Account
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set staffNalme
     *
     * @param string $staffName
     *
     * @return Account
     */
    public function setStaffName($staffName)
    {
        $this->staffName = $staffName;

        return $this;
    }

    /**
     * Get staffName
     *
     * @Groups({"searchable"})
     *
     * @return string
     */
    public function getStaffName()
    {
        return $this->staffName;
    }

    /**
     * Set isInducted
     *
     * @param boolean $isInducted
     *
     * @return Account
     */
    public function setIsInducted($isInducted)
    {
        $this->isInducted = $isInducted;

        return $this;
    }

    /**
     * Get isInducted
     *
     * @return boolean
     */
    public function getIsInducted()
    {
        return $this->isInducted;
    }

    /**
     * @return Collection|Transactions[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transactions $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

        return $this;
    }
}
