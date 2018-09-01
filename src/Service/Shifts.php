<?php

namespace App\Service;

use App\Repository\ShiftRepository;
use App\Repository\TimeclockRepository;

class Shifts // TODO: Better class name?
{
    /**
     * @var ShiftRepository
     */
    private $shiftRepository;

    /**
     * @var TimeclockRepository
     */
    private $timeclockRepository;

    /**
     * @param ShiftRepository $shiftRepository
     * @param TimeclockRepository $timeclockRepository
     */
    public function __construct(ShiftRepository $shiftRepository, TimeclockRepository $timeclockRepository)
    {
        $this->shiftRepository = $shiftRepository;
        $this->timeclockRepository = $timeclockRepository;
    }

    /**
     * Get all shifts for given date by employee, along with associated timeclocks.
     * Data format:
     * [
     *     [
     *         'employee_name' => x,
     *         'employee_id' => x,
     *         'position' => x,
     *         'start_time' => x,
     *         'end_time' => x',
     *         'timeclocks' => [
     *             [
     *                 'in' => x,
     *                 'out' => x'
     *             ],
     *             ...
     *         ]
     *     ]
     * ]
     *
     * @param \DateTime $date
     * @return array
     * @throws \App\Exception\BadApiResponseException
     * @throws \App\Exception\FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShiftsWithTimeclocksForDate(\DateTime $date)
    {
        $shifts = $this->shiftRepository->getShiftsForDate($date);
        $timeclocks = $this->timeclockRepository->getTimeclocksForDate($date);

        $data = [];

        foreach($shifts as $shift) {
            // Get shifts for each employee
            foreach($shift->getEmployees() as $employee) {

                // Basic shift data
                $currentShift = [
                    'employee_name' => $employee['name'],
                    'employee_id' => $employee['id'],
                    'position' => $shift->getPosition(),
                    'start_time' => $shift->getStartTime(),
                    'end_time' => $shift->getEndTime(),
                    'timeclocks' => [],
                ];

                // Go through all timeclocks and try to match them with the current shift
                foreach($timeclocks as $timeclock) {
                    $timeclock->getEmployee()['id'];
                    $timeclock->getScheduleId();

                    if(!empty($timeclock->getShiftId())) {

                        // If shift id is present in the timeclock, check if it matches with the current shift's id
                        if($timeclock->getShiftId() === $shift->getId() && $timeclock->getEmployee()['id'] === $employee['id']) {
                            $currentShift['timeclocks'][] = [
                                'in' => $timeclock->getInTime(),
                                'out' => $timeclock->getOutTime(),
                            ];
                        }
                    } else {

                        // If there is no shift id, check if employee ids and schedule ids match
                        if($timeclock->getEmployee()['id'] === $employee['id'] && $timeclock->getScheduleId() === $shift->getScheduleId()) {
                            $currentShift['timeclocks'][] = [
                                'in' => $timeclock->getInTime(),
                                'out' => $timeclock->getOutTime(),
                            ];
                        }
                    }
                }

                $data[] = $currentShift;
            }
        }

        return $data;
    }
}