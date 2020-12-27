<?php

namespace App\Entity;

use App\Repository\TransactionsRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TransactionsRepository::class)
 */
class Transactions
{
    const METHODS = ['account', 'cash', 'card', 'pumpkin', ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     * @Assert\GreaterThan(0)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=7)
     * @Assert\Choice(choices=Transactions::METHODS, message="Invalid method.")
     */
    private $method;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(
     *     min=1,
     *     max=3,
     *     invalidMessage="Unknown type."
     * )
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Account::class, inversedBy="transactions")
     */
    private $account;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="transactions")
     */
    private $staff;

    /**
     * @ORM\OneToMany(targetEntity=DetailsTransactions::class, mappedBy="transaction", orphanRemoval=true)
     */
    private $details;

    /**
     * @ORM\ManyToOne(targetEntity=Zreport::class, inversedBy="transactions")
     */
    private $zreport;

    public function __construct()
    {
        $this->details = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

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
     * @return Collection|DetailsTransactions[]
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(DetailsTransactions $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details[] = $detail;
            $detail->setTransaction($this);
        }

        return $this;
    }

    public function removeDetail(DetailsTransactions $detail): self
    {
        if ($this->details->removeElement($detail)) {
            // set the owning side to null (unless already changed)
            if ($detail->getTransaction() === $this) {
                $detail->setTransaction(null);
            }
        }

        return $this;
    }

    public function getZreport(): ?Zreport
    {
        return $this->zreport;
    }

    public function setZreport(?Zreport $zreport): self
    {
        $this->zreport = $zreport;

        return $this;
    }
}
