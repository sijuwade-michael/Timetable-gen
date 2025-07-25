<?php

namespace App\Services\GeneticAlgorithm;

class GeneticAlgorithm
{
    private $populationSize;
    private $mutationRate;
    private $crossoverRate;
    private $elitismCount;
    private $tournamentSize;
    private $temperature;
    private $coolingRate;

    public function __construct($populationSize, $mutationRate, $crossoverRate, $elitismCount, $tournamentSize)
    {
        $this->populationSize = $populationSize;
        $this->mutationRate = $mutationRate;
        $this->crossoverRate = $crossoverRate;
        $this->elitismCount = $elitismCount;
        $this->tournamentSize = $tournamentSize;
        $this->temperature = 1.0;
        $this->coolingRate = 0.001;
    }

    public function initPopulation($timetable)
    {
        return new Population($this->populationSize, $timetable);
    }

    public function getTemperature()
    {
        return $this->temperature;
    }

    public function coolTemperature()
    {
        $this->temperature *= (1 - $this->coolingRate);
    }

    public function calculateFitness($individual, $timetable)
    {
        $timetable = clone $timetable;
        $timetable->createClasses($individual);
        $clashes = $timetable->calcClashes();
        $fitness = 1.0 / ($clashes + 1);
        $individual->setFitness($fitness);
        return $fitness;
    }

    public function evaluatePopulation($population, $timetable)
    {
        $populationFitness = 0;
        foreach ($population->getIndividuals() as $individual) {
            $populationFitness += $this->calculateFitness($individual, $timetable);
        }
        $population->setPopulationFitness($populationFitness);
    }

    public function isTerminationConditionMet($population)
    {
        return $population->getFittest(0)->getFitness() == 1.0;
    }

    public function isGenerationsMaxedOut($generation, $maxGenerations)
    {
        return $generation > $maxGenerations;
    }

    public function selectParent($population)
    {
        $tournament = new Population();
        $population->shuffle();

        for ($i = 0; $i < $this->tournamentSize; $i++) {
            $participant = $population->getIndividual($i);
            $tournament->setIndividual($i, $participant);
        }

        return $tournament->getFittest(0);
    }

    public function crossoverPopulation($population)
    {
        $newPopulation = new Population($population->size());

        for ($i = 0; $i < $population->size(); $i++) {
            $parentA = $population->getFittest($i);
            $offspring = clone $parentA;

            $random = mt_rand() / mt_getrandmax();
            if ($this->crossoverRate > $random && $i > $this->elitismCount) {
                $parentB = $this->selectParent($population);
                $swapPoint = mt_rand(0, $parentB->getChromosomeLength());

                for ($j = 0; $j < $parentA->getChromosomeLength(); $j++) {
                    $geneA = $parentA->getGene($j);
                    if (is_numeric($geneA)) {
                        // Only swap numerical genes (e.g., time, room, prof)
                        $geneB = $parentB->getGene($j);
                        $offspring->setGene($j, ($j < $swapPoint) ? $geneA : $geneB);
                    }
                }
            }

            $newPopulation->setIndividual($i, $offspring);
        }

        return $newPopulation;
    }

    public function mutatePopulation($population, $timetable)
    {
        $newPopulation = new Population();
        $bestFitness = $population->getFittest(0)->getFitness();

        for ($i = 0; $i < $population->size(); $i++) {
            $individual = $population->getFittest($i);
            $randomIndividual = new Individual($timetable);

            $adaptiveMutationRate = $this->mutationRate;
            if ($individual->getFitness() > $population->getAvgFitness()) {
                $delta1 = $bestFitness - $individual->getFitness();
                $delta2 = $bestFitness - $population->getAvgFitness();
                $adaptiveMutationRate = ($delta2 > 0) ? ($delta1 / $delta2) * $this->mutationRate : $this->mutationRate;
            }

            if ($i > $this->elitismCount) {
                for ($j = 0; $j < $individual->getChromosomeLength(); $j++) {
                    $gene = $individual->getGene($j);
                    if (is_numeric($gene)) {
                        $random = mt_rand() / mt_getrandmax();
                        if (($adaptiveMutationRate * $this->temperature) > $random) {
                            $individual->setGene($j, $randomIndividual->getGene($j));
                        }
                    }
                }
            }

            $newPopulation->setIndividual($i, $individual);
        }

        return $newPopulation;
    }
}
