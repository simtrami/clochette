<?php

namespace App\Entity;

use App\Repository\TreasuryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TreasuryRepository::class)
 */
class Treasury
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $cashRegister;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    private $safe;

    /**
     * @ORM\OneToOne(targetEntity=Zreport::class, mappedBy="treasury", cascade={"persist", "remove"})
     */
    private $zreport;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCashRegister(): ?string
    {
        return $this->cashRegister;
    }

    public function setCashRegister(string $cashRegister): self
    {
        $this->cashRegister = $cashRegister;

        return $this;
    }

    public function getSafe(): ?string
    {
        return $this->safe;
    }

    public function setSafe(string $safe): self
    {
        $this->safe = $safe;

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
