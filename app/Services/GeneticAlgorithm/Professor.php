<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Professor as ProfessorModel;

class Professor
{
    /**
     * ID of professor
     *
     * @var int
     */
    private $id;

    /**
     * Professor model from db
     *
     * @var ProfessorModel
     */
    private $professorModel;

    /**
     * Timeslots that the professor is unavailable
     *
     * @var array
     */
    private $occupiedSlots;

    /**
     * Create a new professor
     *
     * @param int   $id             ID of professor
     * @param array $occupiedSlots  Timeslots that the professor is not available
     */
    public function __construct($id, $occupiedSlots)
    {
        $this->id = $id;
        $this->professorModel = ProfessorModel::find($this->id);
        $this->occupiedSlots = $occupiedSlots;
    }

    /**
     * Get ID of professor
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name of professor
     *
     * @return string
     */
    public function getName()
    {
        return $this->professorModel->name;
    }

    /**
     * Get unavailable time slots
     *
     * @return array
     */
    public function getOccupiedSlots()
    {
        return $this->occupiedSlots;
    }
}
