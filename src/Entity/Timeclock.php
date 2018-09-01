<?php

namespace App\Entity;

class Timeclock
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $scheduleId;

    /**
     * @var string
     */
    private $inTime;

    /**
     * @var string
     */
    private $outTime;

    /**
     * @var array
     */
    private $employee;

    /**
     * @var int|null
     */
    private $shiftId;

    /**
     * @param int $id
     * @param int $scheduleId
     * @param string $inTime
     * @param string $outTime
     * @param array $employee
     * @param int|null $shiftId
     */
    public function __construct($id, $scheduleId, $inTime, $outTime, $employee, $shiftId)
    {
        $this->id = $id;
        $this->scheduleId = $scheduleId;
        $this->inTime = $inTime;
        $this->outTime = $outTime;
        $this->employee = $employee;
        $this->shiftId = $shiftId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getScheduleId()
    {
        return $this->scheduleId;
    }

    /**
     * @return string
     */
    public function getInTime()
    {
        return $this->inTime;
    }

    /**
     * @return string
     */
    public function getOutTime()
    {
        return $this->outTime;
    }

    /**
     * @return array
     */
    public function getEmployee()
    {
        return $this->employee;
    }

    /**
     * @return int|null
     */
    public function getShiftId()
    {
        return $this->shiftId;
    }
}