<?php

namespace App\Contracts\Interfaces\Operator;

use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Models\Mastercard;

interface MastercardInterface extends GetInterface, StoreInterface, ShowInterface, UpdateInterface, DeleteInterface
{
    // Define your methods here
}
