<?php
        
namespace App\Contracts\Interfaces;
        
use App\Contracts\Interfaces\Eloquent\DeleteInterface; 
use App\Contracts\Interfaces\Eloquent\GetInterface;
use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\ShowInterface; 
use App\Contracts\Interfaces\Eloquent\StoreInterface; 
use App\Contracts\Interfaces\Eloquent\UpdateInterface;
use Illuminate\Http\Request;

interface AttendancePermissionInterface extends GetInterface, StoreInterface, UpdateInterface, ShowInterface, DeleteInterface, PaginateInterface
{
    public function findByStudent(string $studentId, Request $request = null);
    public function deleteIfPending(string $id, string $studentId);
    public function getPendingPermissions();
    public function approvePermission(string $id, string $counselorId);
    public function rejectPermission(string $id, string $counselorId);
    public function searchByCounselor(Request $request);
}