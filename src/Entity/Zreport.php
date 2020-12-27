<?php

namespace App\Entity;

use App\Repository\ZreportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ZreportRepository::class)
 */
class Zreport
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalCommand;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalRefund;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalRefill;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $total;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="zreports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $staff;

    /**
     * @ORM\OneToMany(targetEntity=Transactions::class, mappedBy="zreport")
     */
    private $transactions;

    /**
     * @ORM\OneToOne(targetEntity=Treasury::class, inversedBy="zreport", cascade={"persist", "remove"})
     */
    private $treasury;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalCommand(): ?string
    {
        return $this->totalCommand;
    }

    public function setTotalCommand(string $totalCommand): self
    {
        $this->totalCommand = $totalCommand;

        return $this;
    }

    public function getTotalRefund(): ?string
    {
        return $this->totalRefund;
    }

    public function setTotalRefund(string $totalRefund): self
    {
        $this->totalRefund = $totalRefund;

        return $this;
    }

    public function getTotalRefill(): ?string
    {
        return $this->totalRefill;
    }

    public function setTotalRefill(string $totalRefill): self
    {
        $this->totalRefill = $totalRefill;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getStaff(): ?Users
    {
        return $this->staff;
    }

    public function setStaff(?Users $staff): self
    {
        $this->staff = $staff;

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
            $transaction->setZreport($this);
        }

        return $this;
    }

    public function removeTransaction(Transactions $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getZreport() === $this) {
                $transaction->setZreport(null);
            }
        }

        return $this;
    }

    public function getTreasury(): ?Treasury
    {
        return $this->treasury;
    }

    public function setTreasury(?Treasury $treasury): self
    {
        // unset the owning side of the relation if necessary
        if ($treasury === null && $this->$treasury !== null) {
            $this->treasury->setZreport(null);
        }

        // set the owning side of the relation if necessary
        if ($treasury !== null && $treasury->getZreport() !== $this) {
            $treasury->setZreport($this);
        }

        $this->treasury = $treasury;

        return $this;
    }
}
