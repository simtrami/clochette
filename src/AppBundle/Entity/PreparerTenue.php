<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;

class PreparerTenue
{
    protected $drafts;

    protected $bottles;

    protected $articles;

    public function __construct()
    {
        $this->drafts = new ArrayCollection;
        $this->bottles = new ArrayCollection;
        $this->articles = new ArrayCollection;
    }

    public function getDrafts()
    {
        return $this->drafts;
    }
    public function getBottles()
    {
        return $this->bottles;
    }
    public function getArticles()
    {
        return $this->articles;
    }
}
