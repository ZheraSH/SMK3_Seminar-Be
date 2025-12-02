<?php
        
namespace App\Contracts\Interfaces;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use App\Models\AttendancePermission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface AttendancePermissionInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, PaginateInterface
{
    public function findByStudent(string $studentId, Request $request = null): LengthAwarePaginator;
    public function deleteIfPending(string $id, string $studentId): bool;
    public function getPendingPermissions(): Collection;
    public function approvePermission(string $id, string $counselorId): AttendancePermission;
    public function rejectPermission(string $id, string $counselorId): AttendancePermission;
    public function searchByCounselor(Request $request): LengthAwarePaginator;
    public function count(): int;
    public function getLatest(string $studentId): Collection;

}