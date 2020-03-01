<?php


namespace App\Remindo;


/**
 * Class Question
 * @package App\Remindo
 */
class Question
{
    /**
     * @var string
     */
    public $title    = '';
    /**
     * @var int
     */
    public $maxScore = 0;
    /**
     * @var float|int
     */
    public $avgScore = 0;
    /**
     * @var float|int
     */
    public $pValue = 0;
    /**
     * @var float|int|null
     */
    public $rValue = 0;

    /**
     * Question constructor.
     *
     * @param string $title
     * @param int    $maxScore
     * @param float  $pValue
     * @param float  $rValue
     * @param float  $avgScore
     */
    public function __construct(
        string $title,
        int $maxScore,
        float $avgScore,
        float $pValue,
        ?float $rValue
    )
    {
        $this->title = $title;
        $this->maxScore = (float) $maxScore;
        $this->avgScore = (float) $avgScore;
        $this->pValue = (float) $pValue;
        $this->rValue = $rValue;
    }
}