<?php

namespace App\Contracts\Interfaces;

interface AttendanceMonitoringInterface
{ 
    public function getMonitoringData(array $filters): mixed;
    public function getRecap(array $filters): array;
    public function syncLatestData();
    
}
