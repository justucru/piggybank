<?php

namespace App\Services;

use App\Entity\PiggyBank;
use App\Entity\Transaction;

class TransactionChecker
{
    public function debitOrCredit(string $type, float $amount)
    {
        if ($type == 'debit') {
            return -$amount;
        } elseif ($type == 'credit') {
            return $amount;
        }
    }

    public function isAllowed(PiggyBank $piggybank, string $type, float $amount): bool
    {
        $balance = $piggybank->getBalance();

        $tc = new TransactionChecker();
        $amount = $tc->debitOrCredit($type, $amount);

        if ($balance + $amount < 0 or $balance + $amount > 1000) {
            return false;
        } else {
            return $amount;
        }
    }
}