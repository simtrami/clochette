<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Calendrier
 *
 * @ORM\Table(name="calendrier")
 * @ORM\Entity
 */
class Calendrier
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="tenance", type="string", length=60, nullable=false)
     */
    private $tenance;

    /**
     * @var string
     *
     * @ORM\Column(name="event", type="string", length=60, nullable=true)
     */
    private $event;


    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set tenance
     *
     * @param string $tenance
     *
     * @return Calendrier
     */
    public function setTenance($tenance)
    {
        $this->tenance = $tenance;

        return $this;
    }

    /**
     * Get tenance
     *
     * @return string
     */
    public function getTenance()
    {
        return $this->tenance;
    }

    /**
     * Set event
     *
     * @param string $event
     *
     * @return Calendrier
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }
}
