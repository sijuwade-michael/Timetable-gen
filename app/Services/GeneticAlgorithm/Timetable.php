<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Day;

class Timetable
{
    private $rooms;
    private $professors;
    private $modules;
    private $groups;
    private $timeslots;
    public array $classes;
    private $numClasses;
    public $maxContinuousSlots;

    public function __construct($maxContinuousSlots)
    {
        $this->rooms = [];
        $this->professors = [];
        $this->modules = [];
        $this->groups = [];
        $this->timeslots = [];
        $this->numClasses = 0;
        $this->maxContinuousSlots = $maxContinuousSlots;
    }

    public function getGroups() { return $this->groups; }
    public function getTimeslots() { return $this->timeslots; }
    public function getModules() { return $this->modules; }
    public function getProfessors() { return $this->professors; }

    public function addRoom($roomId) { $this->rooms[$roomId] = new Room($roomId); }

    public function addProfessor($professorId, $unavailableSlots)
    {
        $this->professors[$professorId] = new Professor($professorId, $unavailableSlots);
    }

    public function addModule($moduleId, $professorIds)
    {
        $this->modules[$moduleId] = new Module($moduleId, $professorIds);
    }

    public function addGroup($groupId, $moduleIds)
    {
        $this->groups[$groupId] = new Group($groupId, $moduleIds);
        $this->numClasses = 0;
    }

    public function addTimeslot($timeslotId, $next)
    {
        $this->timeslots[$timeslotId] = new Timeslot($timeslotId, $next);
    }

    // ✅ UPDATED: Create classes with custom session durations
    public function createClasses($individual)
    {
        $classes = [];

        $chromosome = $individual->getChromosome();
        $chromosomePos = 0;
        $classIndex = 0;

        foreach ($this->groups as $id => $group) {
            $moduleIds = $group->getModuleIds();

            foreach ($moduleIds as $moduleId) {
                $module = $this->getModule($moduleId);
                $slots = $module->getSlots($id); // meeting_per_week

                $sessionDurations = [];
                if ($slots == 2) {
                    $sessionDurations = [2];
                } elseif ($slots == 3) {
                    $sessionDurations = [2, 1];
                } elseif ($slots == 4) {
                    $sessionDurations = [2, 2];
                } else {
                    $sessionDurations = array_fill(0, $slots, 1);
                }

                foreach ($sessionDurations as $duration) {
                    for ($j = 0; $j < $duration; $j++) {
                        $classes[$classIndex] = new CollegeClass($classIndex, $group->getId(), $moduleId);

                        $classes[$classIndex]->addTimeslot($chromosome[$chromosomePos++]);
                        $classes[$classIndex]->addRoom($chromosome[$chromosomePos++]);
                        $classes[$classIndex]->addProfessor($chromosome[$chromosomePos++]);

                        $classIndex++;
                    }
                }
            }
        }

        $this->classes = $classes;
    }

    public function getScheme()
    {
        $scheme = [];

        foreach ($this->groups as $id => $group) {
            $moduleIds = $group->getModuleIds();
            $scheme[] = 'G' . $id;

            foreach ($moduleIds as $moduleId) {
                $module = $this->getModule($moduleId);
                for ($i = 1; $i <= $module->getSlots($id); $i++) {
                    $scheme[] = $moduleId;
                }
            }
        }

        return implode(",", $scheme);
    }

    public function getRoom($roomId)
    {
        if (!isset($this->rooms[$roomId])) {
            print "No room with ID " . $roomId;
            return null;
        }

        return $this->rooms[$roomId];
    }

    public function getRooms() { return $this->rooms; }
    public function getRandomRoom() { return $this->rooms[array_rand($this->rooms)]; }
    public function getProfessor($professorId) { return $this->professors[$professorId]; }
    public function getModule($moduleId) { return $this->modules[$moduleId]; }

    public function getGroupModules($groupId)
    {
        $group = $this->groups[$groupId];
        return $group->getModuleIds();
    }

    public function getGroup($groupId) { return $this->groups[$groupId]; }
    public function getTimeslot($timeslotId) { return $this->timeslots[$timeslotId]; }
    public function getRandomTimeslot() { return $this->timeslots[array_rand($this->timeslots)]; }
    public function getClasses() { return $this->classes; }

    public function getNumClasses()
    {
        if ($this->numClasses > 0) return $this->numClasses;

        $numClasses = 0;
        foreach ($this->groups as $group) {
            $numClasses += count($group->getModuleIds());
        }

        $this->numClasses = $numClasses;
        return $numClasses;
    }

    public function getClassesByDay($dayId, $groupId)
    {
        $classes = [];

        foreach ($this->classes as $class) {
            $timeslot = $this->getTimeslot($class->getTimeslotId());

            if ($dayId == $timeslot->getDayId() && $class->getGroupId() == $groupId) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    public function calcClashes()
    {
        $clashes = 0;
        $days = Day::all();

        foreach ($this->classes as $id => $classA) {
            $roomCapacity = $this->getRoom($classA->getRoomId())->getCapacity();
            $groupSize = $this->getGroup($classA->getGroupId())->getSize();
            $professor = $this->getProfessor($classA->getProfessorId());
            $timeslot = $this->getTimeslot($classA->getTimeslotId());

            if ($roomCapacity < $groupSize) $clashes++;

            if (in_array($timeslot->getId(), $professor->getOccupiedSlots())) $clashes++;

            foreach ($this->classes as $id => $classB) {
                if ($classA->getId() != $classB->getId()) {
                    if ($classA->getRoomId() == $classB->getRoomId() &&
                        $classA->getTimeslotId() == $classB->getTimeslotId()) {
                        $clashes++;
                        break;
                    }
                }
            }

            if (in_array($classA->getRoomId(), $this->getGroup($classA->getGroupId())->getUnavailableRooms())) {
                $clashes++;
            }

            foreach ($this->classes as $id => $classB) {
                if ($classA->getId() != $classB->getId()) {
                    if ($classA->getProfessorId() == $classB->getProfessorId() &&
                        $classA->getTimeslotId() == $classB->getTimeslotId()) {
                        $clashes++;
                        break;
                    }
                }
            }

            foreach ($this->classes as $id => $classB) {
                if ($classA->getId() != $classB->getId()) {
                    if ($classA->getGroupId() == $classB->getGroupId() &&
                        $classA->getTimeslotId() == $classB->getTimeslotId()) {
                        $clashes++;
                        break;
                    }
                }
            }
        }

        foreach ($days as $day) {
            foreach ($this->getGroups() as $group) {
                $classes = $this->getClassesByDay($day->id, $group->getId());
                $checkedModules = [];

                foreach ($classes as $classA) {
                    if (!in_array($classA->getModuleId(), $checkedModules)) {
                        $moduleTimeslots = [];

                        foreach ($classes as $classB) {
                            if ($classA->getModuleId() == $classB->getModuleId()) {
                                if ($classA->getRoomId() != $classB->getRoomId()) $clashes++;
                                $moduleTimeslots[] = $classB->getTimeslotId();
                            }
                        }

                        if (!$this->areConsecutive($moduleTimeslots)) $clashes++;

                        $checkedModules[] = $classA->getModuleId();
                    }
                }
            }
        }

        return $clashes;
    }

    public function areConsecutive($numbers)
    {
        sort($numbers);
        $min = $numbers[0];
        $max = $numbers[count($numbers) - 1];

        for ($i = $min; $i <= $max; $i++) {
            if (!in_array($i, $numbers)) return false;
        }

        return true;
    }
}
