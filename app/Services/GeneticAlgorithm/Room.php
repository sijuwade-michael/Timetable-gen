<?php

namespace App\Services\GeneticAlgorithm;

use App\Models\Room as RoomModel;

class Room
{
    /**
     * ID assigned to room
     *
     * @var int
     */
    private $roomId;

    /**
     * Room model from the database
     *
     * @var RoomModel
     */
    private $model;

    /**
     * Create a new room
     *
     * @param int $roomId ID of room
     */
    public function __construct($roomId)
    {
        $this->roomId = $roomId;
        $this->model = RoomModel::find($roomId);
    }

    /**
     * Get the ID of the room
     *
     * @return int
     */
    public function getId()
    {
        return $this->roomId;
    }

    /**
     * Get the room's number (name)
     *
     * @return string
     */
    public function getRoomNumber()
    {
        return $this->model->name;
    }

    /**
     * Get the capacity of the room
     *
     * @return int
     */
    public function getCapacity()
    {
        return $this->model->capacity;
    }
}
