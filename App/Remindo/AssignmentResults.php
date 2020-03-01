<?php

namespace App\Remindo;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;

use MathPHP\Exception\OutOfBoundsException;
use MathPHP\NumericalAnalysis\Interpolation;
use MathPHP\Exception\BadDataException;
use MathPHP\Exception\IncorrectTypeException;
use MathPHP\NumericalAnalysis\Interpolation\NewtonPolynomialForward;
use MathPHP\Statistics\Correlation;

/**
 * Class assignmentResults
 * @package App\Remindo
 */
class assignmentResults
{
    /** name of this test
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $maxTotalScore = 0;

    /** Questions
     * @var array
     */
    public $questions = [];

    /** Percentile to grade interpolation points
     * @var array
     */
    public $gradeInterPoints = [];

    /**
     * @var NewtonPolynomialForward
     */
    private $p;

    /**
     * @var int
     */
    public $numberOfQuestions = 0;

    /**
     * @var int
     */
    public $numberOfStudents = 0;

    /** Array of Student & individual assignment results + grade
     * @var array
     */
    public $students = [];

    /**
     * assignment constructor.
     *
     * @param String $assignmentName name of this assignment
     * @param array $gradeInterPoints interpolation table for percentage to grade
     *
     * @throws BadDataException
     * @throws IncorrectTypeException
     */
    public function __construct(string $assignmentName, Array $gradeInterPoints)
    {
        $this->name = $assignmentName;
        $this->gradeInterPoints = $gradeInterPoints;
        $this->p = Interpolation\NewtonPolynomialForward::interpolate($gradeInterPoints);
    }

    // @todo more separation of concerns

    /** Imports XLS file assignment information and create Students with grades
     *
     * @param string $filePath
     *
     * @return bool
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     */
    public function importFromXLSFile(string $filePath): bool
    {
        // @todo cleanup this function and separate concerns
        $questionTitles = [];
        $maxScores = [];
        $answersTable = [];

        $numberOfQuestions = 0;
        $numberOfStudents = 0;

        if (!file_exists($filePath)) {
            return false;
        }

        // Open XLS
        $reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $reader->open($filePath);
        foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
            // only process sheet 1
            if ($sheetIndex > 1) {
                break;
            }


            foreach ($sheet->getRowIterator() as $rowIndex => $row) {

                /** @noinspection PhpUndefinedMethodInspection */
                $cells = $row->getCells();

                // continue at any row containing empty first cell
                if ($cells[0] === '') {
                    continue;
                }

                switch ((int)$rowIndex) {
                    case 1:
                        // Row 1 contains question titles
                        $questionTitles = $this->reMapToString($cells);
                        break;
                    case 2:
                        // Row 2 contains max score for each question
                        $maxScores = $this->reMapToInteger($cells);
                        $this->maxTotalScore = array_sum($maxScores);
                        break;
                    default:
                        // All other rows contain student score results
                        $rowOffsetIndex = $rowIndex - 3;
                        $name = (string)$cells[0];
                        $answers = $this->reMapToInteger($cells);

                        foreach ($answers as $answerIndex => $answer) {
                            $answersTable[$answerIndex][$rowOffsetIndex] = $answer;
                        }

                        // Create student and calculate grade
                        $this->students[] = new Student(
                            $name,
                            $answers,
                            $maxScores,
                            $this->maxTotalScore,
                            $this->p
                        );

                        ++$numberOfStudents;
                }
            }
        }

        // Get gradeResult score of all students
        $scores = [];
        foreach ($this->students as $studentIndex => $student) {
            $scores[] = $student->gradeResult->score;
        }

        // Create questions
        foreach ($questionTitles as $questionIndex => $title) {
            $avgScore = 0;
            $pValue = 0;

            $questionAnswers = $answersTable[$questionIndex];
            $maxScore = $maxScores[$questionIndex];
            $questionAnswerCount = count($questionAnswers);
            $sumOfAnswers = array_sum($questionAnswers);

            if ($questionAnswerCount > 0) {
                $avgScore = $sumOfAnswers / $questionAnswerCount;
                $pValue = $avgScore / $maxScore;
            }

            // @todo validate with known...
            $rValue = 0;
            if ((count(array_unique($questionAnswers)) !== 1) &&
                (count(array_unique($scores)) !== 1)) {
                try {
                    $rValue = Correlation::r($scores, $questionAnswers);
                    if (is_nan($rValue)) {
                        $rValue = 0;
                    }
                } catch (BadDataException $e) {
                    $rValue = 0;
                } catch (OutOfBoundsException $e) {
                    $rValue = 0;
                }
            }


            $this->questions[] = new Question($title, $maxScore, $avgScore, $pValue, $rValue);
            ++$numberOfQuestions;
        }

        $this->numberOfQuestions = $numberOfQuestions;
        $this->numberOfStudents = $numberOfStudents;

        // return true if we have results and students
        return $this->numberOfQuestions > 0 && $this->numberOfStudents > 0;
    }

    /**
     * dirty helper to cast excel sheet cell object to string
     *
     * @param array $input
     * @param int $begin
     * @param int $end
     *
     * @return array
     * @todo maybe use different XLS api
     *
     */
    private function reMapToString(Array $input, $begin = 1, $end = -1): array
    {
        return array_map('strval', array_slice($input, $begin, $end));
    }

    /** dirty helper to cast excel sheet string to int
     *
     * @param array $input
     * @param int $begin
     * @param int $end
     *
     * @return array
     * @todo maybe use different XLS api
     *
     */
    private function reMapToInteger(Array $input, $begin = 1, $end = -1): array
    {
        return array_map('intval', $this->reMapToString($input, $begin, $end));
    }

}