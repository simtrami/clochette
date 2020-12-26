<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DetailsTransactions
 *
 * @ORM\Table(name="details_transactions",
 *  indexes={
 *      @ORM\Index(name="transaction", columns={"transaction"}),
 *      @ORM\Index(name="article", columns={"article"})
 *  }
 * )
 * @ORM\Entity
 */
class DetailsTransactions
{
    /**
     * @var \App\Entity\Transactions
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Transactions", inversedBy="details")
     * @ORM\JoinColumn(name="transaction", referencedColumnName="id")
     */
    private $transaction;
    
    /**
     * @var \App\Entity\Stocks
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumn(name="article", referencedColumnName="id")
     */
    private $article;

    /**
     * @var integer
     * 
     * @ORM\Column(name="quantite", type="smallint")
     * @Assert\GreaterThan(0)
     */
    private $quantite;

    ## Fonctions

    /**
     * Set transaction
     *
     * @param Transactions $transaction
     * @return DetailsTransactions
     */
    public function setTransaction(Transactions $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction
     *
     * @return \App\Entity\Transactions
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set article
     *
     * @param Stocks $article
     * @return DetailsTransactions
     */
    public function setArticle(Stocks $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \App\Entity\Stocks
     */
    public function getArticle()
    {
        return $this->article;
    }
    
    /**
     * Set quantite
     *
     * @param integer $quantite
     *
     * @return DetailsTransactions
     */
    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Get quantite
     *
     * @return integer
     */
    public function getQuantite()
    {
        return $this->quantite;
    }
}
