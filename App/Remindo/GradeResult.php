<?php


namespace App\Remindo;


class GradeResult
{
    /** Grade based on percentage 1.0 to 10.0
     * @var float
     */
    public $grade = 0.0;

    /** Sum of this students test results
     * @var int
     */
    public $score = 0;

    /** Percentage of score compared to totalMaxScore
     * @var int
     */
    public $percentage = 0;

    /** Student has passed
     * @var bool
     */
    public $passed = false;


    /**
     * Grade constructor.
     *
     * @param float $grade
     * @param int   $score
     * @param int   $percentage
     * @param bool  $passed
     */
    public function __construct(float $grade, int $score, int $percentage, bool $passed)
    {
        $this->grade = $grade;
        $this->score = $score;
        $this->percentage = $percentage;
        $this->passed = $passed;
    }


}