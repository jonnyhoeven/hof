<?php

namespace App\Remindo;

use MathPHP\Functions\Polynomial;

/**
 * Class Student
 * @package App\Remindo
 */
class Student
{
    /**
     * Name of student
     * @var string
     */
    public $name = '';

    /** Student answers score
     * @var array
     */
    private $answers;

    /** number of answers
     * @var int
     */
    public $numberOfAnswers = 0;

    /** Grade Result
     * @var GradeResult|null
     */
    public $gradeResult;

    /** Contains error messages encountered during grading
     * @var array
     */
    public $messages = [];

    /**
     * Student constructor.
     *
     * @param string $name Student name
     * @param array $testResults test score results
     * @param array $maxScores
     * @param int $maxTotalScore
     * @param Polynomial $p
     */
    public function __construct(
        string $name,
        array $testResults,
        array $maxScores,
        int $maxTotalScore,
        Polynomial $p)
    {
        $this->name = $name;
        $this->answers = $testResults;
        $this->numberOfAnswers = $this->numberOfResults();
        $this->gradeResult = $this->calculateGrade($maxTotalScore, $maxScores, $p);
    }

    // @todo maybe move this function to Test or GradeResult classes
    // @todo more separation of concerns
    /**
     * Return calculated grade results
     *
     * @param int $maxTotalScore
     * @param array $maxScores
     * @param Polynomial $p
     *
     * @return array|null
     */
    private function calculateGrade(int $maxTotalScore, array $maxScores, Polynomial $p): ?GradeResult
    {
        // max total score cannot be 0 (divide by zero)
        if ($maxTotalScore <= 0) {
            $this->messages[] = 'Invalid maxTotalScore';
            return null;
        }
        // We need max scores
        if (!is_array($maxScores) || count($maxScores) === 0) {
            $this->messages[] = 'Invalid maxScores';
            return null;
        }
        // We need at least one student answer
        if (!is_array($this->answers) || count($this->answers) === 0) {
            $this->messages[] = 'Invalid test results';
            return null;
        }
        // The total number of answers array cannot be bigger than count of maxScores array
        if (count($this->answers) > count($maxScores)) {
            $this->messages[] = 'More answer results than questions';
            return null;
        }
        // Validate individual questions and sum totalscore
        $totalScore = 0;
        foreach ($this->answers as $questionIndex => $questionScore) {
            // Score is negative
            if ($questionScore < 0) {
                $this->messages[] = 'Student has has a negative score on index: ' . $questionIndex;
                return null;
            }
            // Score is higher than individual question maxScore
            if ($maxScores[$questionIndex] < $questionScore) {
                $this->messages[] = 'Student has scored higher than should be possible on index: ' . $questionIndex;
                return null;
            }
            $totalScore += $questionScore;
        }
        // check if totalScore is not high than sum of maxScores
        if ($totalScore > $maxTotalScore) {
            $this->messages[] = 'Student scored too high on total (' . $totalScore . '/' . $maxTotalScore . ')';
            return null;
        }
        // calc totalScore percentage of maxTotalScore
        $prc = floor(($totalScore * 100) / $maxTotalScore);

        // get grade from percentage correct using Polynomial
        $grade = number_format($p($prc), 1);

        // check if user has passed
        $passed = false;
        if ($grade >= 5.5) {
            $passed = true;
        }

        // Create gradeResult object
        return new GradeResult($grade, $totalScore, $prc, $passed);
    }

    /** Return number of results or 0
     * @return int
     */
    private function numberOfResults(): int
    {
        if (is_array($this->answers)) {
            return count($this->answers);
        }
        return 0;
    }


}