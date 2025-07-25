<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Course;
use App\Models\CollegeClass as CollegeClassModel;

class Module
{
    /**
     * Id of module
     *
     * @var int
     */
    private $moduleId;

    /**
     * Module's model instance
     *
     * @var Course
     */
    private $moduleModel;

    /**
     * IDs of professors handling this course
     *
     * @var array
     */
    private $professorIds;

    /**
     * Number of allocations done for this module so far
     *
     * @var int
     */
    private $allocatedSlots;

    /**
     * Create a new module
     *
     * @param int   $moduleId      ID of module or course
     * @param array $professorIds  Professors treating this module
     */
    public function __construct($moduleId, $professorIds)
    {
        $this->moduleId = $moduleId;
        $this->moduleModel = Course::find($moduleId);
        $this->professorIds = $professorIds;
        $this->allocatedSlots = 0;
    }

    /**
     * Get ID of a module
     *
     * @return int
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Get the code of the module
     *
     * @return string
     */
    public function getModuleCode()
    {
        return $this->moduleModel->course_code;
    }

    /**
     * Get the module name
     *
     * @return string
     */
    public function getName()
    {
        return $this->moduleModel->name;
    }

    /**
     * Get the number of class sessions to schedule based on meetings per week
     *
     * For example:
     * - 2 meetings → 1 session (2-hour class)
     * - 3 meetings → 2 sessions (1x 2hr, 1x 1hr)
     * - 4 meetings → 2 sessions (2x 2hr)
     *
     * @param int $groupId
     * @return int Number of class sessions
     */
    public function getSlots($groupId)
    {
        $group = CollegeClassModel::find($groupId);
        $meetings = $group->courses()->where('courses.id', $this->moduleId)->first()->pivot->meetings;

        // Convert total hours into number of sessions
        if ($meetings == 2) {
            return 1; // one double class
        } elseif ($meetings == 3) {
            return 2; // one double, one single
        } elseif ($meetings == 4) {
            return 2; // two double classes
        }

        // fallback: treat each hour as a session
        return $meetings;
    }

    /**
     * Get the number of allocated class sessions so far
     *
     * @return int
     */
    public function getAllocatedSlots()
    {
        return $this->allocatedSlots;
    }

    /**
     * Reset allocated slot counter
     *
     * @return void
     */
    public function resetAllocated()
    {
        $this->allocatedSlots = 0;
    }

    /**
     * Increase the number of allocated slots
     *
     * @return void
     */
    public function increaseAllocatedSlots()
    {
        $this->allocatedSlots += 1;
    }

    /**
     * Get a random professor that can teach this module
     *
     * @return int
     */
    public function getRandomProfessorId()
    {
        $pos = rand(0, count($this->professorIds) - 1);
        return $this->professorIds[$pos];
    }
}
