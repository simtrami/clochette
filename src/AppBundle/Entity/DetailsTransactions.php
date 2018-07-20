<?php

namespace AppBundle\Entity;

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
     * @var \AppBundle\Entity\Transactions
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Transactions", inversedBy="details")
     * @ORM\JoinColumn(name="transaction", referencedColumnName="id")
     */
    private $transaction;
    
    /**
     * @var \AppBundle\Entity\Stocks
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Stocks")
     * @ORM\JoinColumn(name="article", referencedColumnName="id")
     */
    private $article;

    /**
     * @var integer
     * 
     * @ORM\Column(name="quantite", type="integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $quantite;

    ## Fonctions

    /**
     * Set transaction
     *
     * @param \AppBundle\Entity\Transactions $id
     *
     * @return DetailsTransactions
     */
    public function setTransaction(\AppBundle\Entity\Transactions $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction
     *
     * @return \AppBundle\Entity\Transactions
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
    
    /**
     * Set article
     *
     * @param \AppBundle\Entity\Stocks $id
     *
     * @return DetailsTransactions
     */
    public function setArticle(\AppBundle\Entity\Stocks $article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Get article
     *
     * @return \AppBundle\Entity\Stocks
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
