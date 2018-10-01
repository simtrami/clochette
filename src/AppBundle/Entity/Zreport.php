<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Zreport
 *
 * @ORM\Table(name="zreport", indexes={
 *     @ORM\Index(name="user", columns={"user"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ZreportRepository")
 */
class Zreport
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \string
     *
     * @ORM\Column(name="total_command", type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalCommand;

    /**
     * @var string
     *
     * @ORM\Column(name="total_refund", type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalRefund;

    /**
     * @var string
     *
     * @ORM\Column(name="total_refill", type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $totalRefill;

    /**
     * @var string
     *
     * @ORM\Column(name="total", type="decimal", precision=8, scale=2)
     * @Assert\GreaterThanOrEqual(0)
     */
    private $total;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime", unique=true, options={"default" : "2017-12-12 05:40:42"})
     */
    private $timestamp;

    /**
     * @var \AppBundle\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Transactions", mappedBy="zreport")
     */
    private $transactions;

    /**
     * @var Treasury
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Treasury", mappedBy="zreport")
     */
    private $treasury;

    /**
     * Zreport constructor.
     */
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set totalCommand
     *
     * @param string $totalCommand
     *
     * @return Zreport
     */
    public function setTotalCommand($totalCommand)
    {
        $this->totalCommand = $totalCommand;

        return $this;
    }

    /**
     * Get totalCommand
     *
     * @return string
     */
    public function getTotalCommand()
    {
        return $this->totalCommand;
    }

    /**
     * Set totalRefund
     *
     * @param string $totalRefund
     *
     * @return Zreport
     */
    public function setTotalRefund($totalRefund)
    {
        $this->totalRefund = $totalRefund;

        return $this;
    }

    /**
     * Get totalRefund
     *
     * @return string
     */
    public function getTotalRefund()
    {
        return $this->totalRefund;
    }

    /**
     * Set totalRefill
     *
     * @param string $totalRefill
     *
     * @return Zreport
     */
    public function setTotalRefill($totalRefill)
    {
        $this->totalRefill = $totalRefill;

        return $this;
    }

    /**
     * Get totalRefill
     *
     * @return string
     */
    public function getTotalRefill()
    {
        return $this->totalRefill;
    }

    /**
     * Set total
     *
     * @param string $total
     *
     * @return Zreport
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set timestamp.
     *
     * @param \DateTime $timestamp
     *
     * @return Zreport
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set user.
     *
     * @param $user
     *
     * @return Zreport
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \AppBundle\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get transactions.
     *
     * @return ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set treasury
     *
     * @param $treasury
     *
     * @return Zreport
     */
    public function setTreasury($treasury)
    {
        $this->treasury = $treasury;

        return $this;
    }

    /**
     * Get treasury
     *
     * @return \AppBundle\Entity\Treasury
     */
    public function getTreasury()
    {
        return $this->treasury;
    }
}
