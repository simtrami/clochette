<?php

namespace App\Entity;

use App\Repository\DetailsTransactionsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DetailsTransactionsRepository::class)
 */
class DetailsTransactions
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\GreaterThan(0)
     */
    private $quantity;

    /**
     * @ORM\ManyToOne(targetEntity=Stocks::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity=Transactions::class, inversedBy="details")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getArticle(): ?Stocks
    {
        return $this->article;
    }

    public function setArticle(?Stocks $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getTransaction(): ?Transactions
    {
        return $this->transaction;
    }

    public function setTransaction(?Transactions $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }
}
