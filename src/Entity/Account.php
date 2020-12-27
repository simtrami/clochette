<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AccountRepository::class)
 */
class Account
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "This field must be more than {{ limit }} characters.",
     *      maxMessage = "This field name cannot be more than {{ limit }} characters."
     * )
     */
    private $pseudo;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $balance;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThanOrEqual(1)
     */
    private $year;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 30,
     *      minMessage = "The staff name must be more than {{ limit }} characters.",
     *      maxMessage = "The staff name cannot be more than {{ limit }} characters."
     * )
     */
    private $staffName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInducted = false;

    /**
     * @ORM\OneToMany(targetEntity=Transactions::class, mappedBy="account")
     */
    private $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getStaffName(): ?string
    {
        return $this->staffName;
    }

    public function setStaffName(?string $staffName): self
    {
        $this->staffName = $staffName;

        return $this;
    }

    public function getIsInducted(): ?bool
    {
        return $this->isInducted;
    }

    public function setIsInducted(bool $isInducted): self
    {
        $this->isInducted = $isInducted;

        return $this;
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
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

        return $this;
    }
}
