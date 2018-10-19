<?php

namespace AppBundle\CheckAccount;

use Appbundle\Entity\Account;

class CheckAccount{

    public function anneeNotValid(Account $compte){

        return $compte->getYear()<1;
    } 

    public function namesValid(Account $compte){
        return (ctype_alpha($compte->getLastName()) &&
        ctype_alpha($compte->getFirstName()) &&
        ctype_alpha($compte->getPseudo() 
        /* ne prend pas en compte 
        les accents et cédilles */
    ));
    }
}
