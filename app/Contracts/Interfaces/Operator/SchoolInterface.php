<?php

namespace App\Contracts\Interfaces\Operator;

use App\Contracts\Interfaces\Eloquent\DeleteInterface;
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface;
use App\Contracts\Interfaces\Eloquent\StoreInterface;
use App\Contracts\Interfaces\Eloquent\UpdateInterface;

interface SchoolInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface 
{
    // Define your methods here
}