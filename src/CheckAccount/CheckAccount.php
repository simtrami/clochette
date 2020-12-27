<?php

namespace App\CheckAccount;

use App\Entity\Account;

class CheckAccount{

    public function anneeNotValid(Account $account): bool
    {
        return $account->getYear()<1;
    }

    public function namesValid(Account $account): bool
    {
        return (
            ctype_alpha($account->getLastName()) &&
            ctype_alpha($account->getFirstName()) &&
            ctype_alpha($account->getPseudo()
            /* ne prend pas en compte
            les accents et cédilles */
            ));
    }
}
