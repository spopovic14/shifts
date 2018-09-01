<?php

namespace App\Entity;


class Shift
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
    private $position;

    /**
     * @var string
     */
    private $startTime;

    /**
     * @var string
     */
    private $endTime;

    /**
     * @var array
     */
    private $employees;

    /**
     * @param int $id
     * @param int $scheduleId
     * @param string $position
     * @param string $startTime
     * @param string $endTime
     * @param array $employees
     */
    public function __construct($id, $scheduleId, $position, $startTime, $endTime, $employees)
    {
        $this->id = $id;
        $this->scheduleId = $scheduleId;
        $this->position = $position;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->employees = $employees;
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
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return string
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @return array
     */
    public function getEmployees()
    {
        return $this->employees;
    }
}