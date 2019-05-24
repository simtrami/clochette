<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Treasury
 *
 * @ORM\Table(name="treasury")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TreasuryRepository")
 */
class Treasury
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
     * @var string
     *
     * @ORM\Column(name="caisse", type="decimal", precision=8, scale=2, options={"default": 0.00})
     */
    private $caisse = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="coffre", type="decimal", precision=8, scale=2, options={"default": 0.00})
     */
    private $coffre = '0.00';

    /**
     * @var Zreport
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Zreport", inversedBy="treasury")
     * @ORM\JoinColumn(name="zreport_id", referencedColumnName="id")
     */
    private $zreport;

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
     * Set caisse.
     *
     * @param string $caisse
     *
     * @return Treasury
     */
    public function setCaisse($caisse)
    {
        $this->caisse = $caisse;

        return $this;
    }

    /**
     * Get caisse.
     *
     * @return string
     */
    public function getCaisse()
    {
        return $this->caisse;
    }

    /**
     * Set coffre.
     *
     * @param string $coffre
     *
     * @return Treasury
     */
    public function setCoffre($coffre)
    {
        $this->coffre = $coffre;

        return $this;
    }

    /**
     * Get coffre.
     *
     * @return string
     */
    public function getCoffre()
    {
        return $this->coffre;
    }

    /**
     * Set zreport.
     *
     * @param $zreport
     *
     * @return Treasury
     */
    public function setZreport($zreport)
    {
        $this->zreport = $zreport;

        return $this;
    }

    /**
     * Get zreport.
     *
     * @return Zreport
     */
    public function getZreport()
    {
        return $this->zreport;
    }
}
