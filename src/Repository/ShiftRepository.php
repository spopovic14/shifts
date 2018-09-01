<?php

namespace App\Repository;

use App\Entity\Shift;
use App\Service\HumanityApi;

class ShiftRepository
{
    /**
     * @var HumanityApi
     */
    private $api;

    /**
     * @param HumanityApi $api
     */
    public function __construct(HumanityApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param \DateTime $date
     * @return array|Shift[]
     * @throws \App\Exception\BadApiResponseException
     * @throws \App\Exception\FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getShiftsForDate(\DateTime $date)
    {
        $data = $this->api->getJsonResponse('GET', '/shifts', [
            'query' => [
                'start_date' => $date->format('Y-m-d'),
                'end_date' => $date->format('Y-m-d'),
            ],
        ]);

        // TODO: Error handling for different status codes?
        if($data['status'] !== 1) {
            return [];
        }

        if(empty($data['data'])) {
            return [];
        }

        $shifts = [];

        foreach($data['data'] as $shiftArray) {

            // Get id
            $id = $shiftArray['id'];

            // Get schedule id
            $scheduleId = $shiftArray['schedule'];

            // Get employees
            $employees = [];

            if(!empty($shiftArray['employees'])) {
                foreach($shiftArray['employees'] as $employeeArray) {
                    $employeeName = $employeeArray['name'];
                    $employeeId = $employeeArray['id'];

                    $employees[] = [
                        'name' => $employeeName,
                        'id' => $employeeId,
                    ];
                }
            }

            // Get position
            $position = $shiftArray['schedule_name'];

            // Get start time
            $startTime = $shiftArray['start_date']['time'];

            // Get end time
            $endTime = $shiftArray['end_date']['time'];

            $shifts[] = new Shift($id, $scheduleId, $position, $startTime, $endTime, $employees);
        }

        return $shifts;
    }
}