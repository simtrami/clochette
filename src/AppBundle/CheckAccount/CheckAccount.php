<?php

namespace AppBundle\CheckAccount;

use Appbundle\Entity\Comptes;

class CheckAccount{

    public function anneeNotValid(Comptes $compte){

        return $compte->getAnnee()<1;
    } 

    public function namesValid(Comptes $compte){
        return (is_string($compte->getNom()) && is_string($compte->getPrenom()) && is_string($compte->getPseudo()));
    }
}