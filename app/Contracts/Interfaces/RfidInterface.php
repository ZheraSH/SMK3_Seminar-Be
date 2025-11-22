<?php
        
namespace App\Contracts\Interfaces;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use App\Models\Rfid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface RfidInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, SearchInterface, PaginateInterface
{
    public function getAvailableStudents(Request $request): Collection;
    public function getByStudentId(string $studentId): ?Rfid;
    public function getByRfidNumber(string $rfid): ?Rfid;
    public function used(): Collection;
    public function notUsed(): Collection;
    public function count(): int;
}