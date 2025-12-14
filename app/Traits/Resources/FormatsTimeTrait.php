<?php
namespace App\Traits\Resources;

trait FormatsTimeTrait
{
    private function formatTime($time): string
    {
        if (!$time) return '';
        if ($time instanceof \DateTime || $time instanceof \Carbon\Carbon) {
            return $time->format('H.i');
        }
        if (is_string($time) && preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return $matches[1] . ':' . $matches[2];
        }
        return (string) $time;
    }

    private function formatTimeRange($startTime, $endTime): string
    {
        if (!$startTime || !$endTime) {
            return '-';
        }
        
        $start = $this->formatTime($startTime);
        $end = $this->formatTime($endTime);
        
        return $start && $end ? $start . ' - ' . $end : '-';
    }
}