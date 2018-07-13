<?php

namespace AppBundle\CheckAccount;

use Appbundle\Entity\Comptes;

class CheckAccount{

    public function anneeNotValid(Comptes $compte){

        return $compte->getAnnee()<1;
    } 

    public function namesValid(Comptes $compte){
        return (ctype_alpha($compte->getNom()) && 
        ctype_alpha($compte->getPrenom()) && 
        ctype_alpha($compte->getPseudo() 
        /* ne prend pas en compte 
        les accents et cédilles */
    ));
    }
}