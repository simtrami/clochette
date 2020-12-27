<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class SellsManagement
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

    public function getDrafts(): ArrayCollection
    {
        return $this->drafts;
    }
    public function getBottles(): ArrayCollection
    {
        return $this->bottles;
    }
    public function getArticles(): ArrayCollection
    {
        return $this->articles;
    }
}
