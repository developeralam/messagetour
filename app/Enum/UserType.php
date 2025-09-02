<?php

namespace App\Enum;

enum UserType: int
{
    case Admin = 1;
    case Agent = 2;
    case Customer = 3;
    case Accountant = 4;
    case Reservation = 5;
    case Manager = 6;
    case Supplier = 7;
}
