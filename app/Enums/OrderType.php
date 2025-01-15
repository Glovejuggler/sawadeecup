<?php

namespace App\Enums;

enum OrderType: string
{
    case DINEIN = 'Dine-in';
    case TAKEOUT = 'Take-out';
}