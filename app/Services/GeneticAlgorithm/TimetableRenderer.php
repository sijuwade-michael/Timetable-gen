<?php

namespace App\Services\GeneticAlgorithm;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Day as DayModel;
use App\Models\Room as RoomModel;
use App\Models\Course as CourseModel;
use App\Models\Timeslot as TimeslotModel;
use App\Models\CollegeClass as CollegeClassModel;
use App\Models\Professor as ProfessorModel;

class TimetableRenderer
{
    protected $timetable;

    /**
     * Create a new instance of this class
     */
    public function __construct($timetable)
    {
        $this->timetable = $timetable;
    }

    /**
     * Generate HTML layout files out of the timetable data
     */
    public function render()
    {
        $chromosome = explode(",", $this->timetable->chromosome);
        $scheme = explode(",", $this->timetable->scheme);
        $data = $this->generateData($chromosome, $scheme);

        $days = $this->timetable->days()->orderBy('id', 'ASC')->get();
        $timeslots = TimeslotModel::orderBy('rank', 'ASC')->get();
        $classes = CollegeClassModel::all();

        $tableTemplate = '<h3 class="text-center">{TITLE}</h3>
                         <div style="page-break-after: always">
                            <table class="table table-bordered">
                                <thead>
                                    {HEADING}
                                </thead>
                                <tbody>
                                    {BODY}
                                </tbody>
                            </table>
                        </div>';

        $content = "";

        foreach ($classes as $class) {
            $header = "<tr class='table-head'><td>Days</td>";

            foreach ($timeslots as $timeslot) {
                $header .= "<td>" . $timeslot->time . "</td>";
            }

            $header .= "</tr>";
            $body = "";

            foreach ($days as $day) {
                $body .= "<tr><td>" . strtoupper($day->short_name) . "</td>";
                foreach ($timeslots as $timeslot) {
                    $cellData = $data[$class->id][$day->name][$timeslot->time] ?? null;
                    if ($cellData) {
                        $body .= "<td class='text-center'>";
                        $body .= "<span class='course_code'>{$cellData['course_code']}</span><br />";
                        $body .= "<span class='room pull-left'>{$cellData['room']}</span>";
                        $body .= "<span class='professor pull-right'>{$cellData['professor']}</span>";
                        $body .= "</td>";
                    } else {
                        $body .= "<td></td>";
                    }
                }
                $body .= "</tr>";
            }

            $title = $class->name;
            $content .= str_replace(['{TITLE}', '{HEADING}', '{BODY}'], [$title, $header, $body], $tableTemplate);
        }

        $path = 'public/timetables/timetable_' . $this->timetable->id . '.html';
        Storage::put($path, $content);

        $this->timetable->update([
            'file_url' => $path
        ]);
    }

    /**
     * Generate data structure from chromosome and scheme
     */
    public function generateData($chromosome, $scheme)
    {
        $data = [];
        $schemeIndex = 0;
        $chromosomeIndex = 0;
        $groupId = null;

        while ($chromosomeIndex < count($chromosome) && $schemeIndex < count($scheme)) {
            // Find next groupId
            while (isset($scheme[$schemeIndex]) && str_starts_with($scheme[$schemeIndex], 'G')) {
                $groupId = substr($scheme[$schemeIndex], 1);
                $schemeIndex++;
            }

            $courseId = $scheme[$schemeIndex] ?? null;
            $schemeIndex++;

            if (!$groupId || !$courseId) {
                Log::warning("Missing groupId or courseId in scheme. Skipping...");
                $chromosomeIndex += 3;
                continue;
            }

            $class = CollegeClassModel::find($groupId);
            $course = CourseModel::find($courseId);

            $timeslotGene = $chromosome[$chromosomeIndex] ?? null;
            $roomGene = $chromosome[$chromosomeIndex + 1] ?? null;
            $professorGene = $chromosome[$chromosomeIndex + 2] ?? null;
            $chromosomeIndex += 3;

            if (!$timeslotGene || !$roomGene || !$professorGene) {
                Log::warning("Incomplete gene triplet for group $groupId, course $courseId. Skipping...");
                continue;
            }

            preg_match('/D(\d+)T(\d+)/', $timeslotGene, $matches);
            if (count($matches) < 3) {
                Log::warning("Invalid timeslot gene format: $timeslotGene");
                continue;
            }

            $dayId = $matches[1];
            $timeslotId = $matches[2];

            $day = DayModel::find($dayId);
            $timeslot = TimeslotModel::find($timeslotId);
            $professor = ProfessorModel::find($professorGene);
            $room = RoomModel::find($roomGene);

            if (!$day || !$timeslot || !$professor || !$room || !$class || !$course) {
                Log::warning("One or more related models not found", [
                    'dayId' => $dayId,
                    'timeslotId' => $timeslotId,
                    'professorId' => $professorGene,
                    'roomId' => $roomGene,
                    'groupId' => $groupId,
                    'courseId' => $courseId
                ]);
                continue;
            }

            if (!isset($data[$groupId])) $data[$groupId] = [];
            if (!isset($data[$groupId][$day->name])) $data[$groupId][$day->name] = [];
            if (!isset($data[$groupId][$day->name][$timeslot->time])) $data[$groupId][$day->name][$timeslot->time] = [];

            $data[$groupId][$day->name][$timeslot->time] = [
                'course_code' => $course->course_code,
                'course_name' => $course->name,
                'room' => $room->name,
                'professor' => $professor->name
            ];
        }

        return $data;
    }
}
