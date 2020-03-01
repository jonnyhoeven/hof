<?php
use App\Remindo\AssignmentResults;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use MathPHP\Exception\BadDataException;
use MathPHP\Exception\IncorrectTypeException;

require_once(__DIR__ . '/vendor/autoload.php');

// Name of assignment
$assignmentName = 'Test 1';
// XLS filename
$filename = '/data/Assignment.xlsx';
// Interpolate score/maxScore percentage to grade
$gradeInterPoints = [
    [0, 1],
    [20, 1],
    [70, 5.5],
    [100, 10],
];

// Construct AssignmentResults
try {
    $myResults = new AssignmentResults($assignmentName, $gradeInterPoints);
} catch (BadDataException $e) {
    die('BadDataException');
} catch (IncorrectTypeException $e) {
    die('IncorrectTypeException');
}

// Import XLS data into AssignmentResults
try {
    if (!$myResults->importFromXLSFile(__DIR__ . $filename)) {
        die('Cannot import file...');
    }
} catch (IOException $e) {
    die('IOException');
} catch (UnsupportedTypeException $e) {
    die('UnsupportedTypeException');
} catch (ReaderNotOpenedException $e) {
    die('ReaderNotOpenedException');
}

// Return JSON header in case http is used
header('Content-Type: application/json');

// Return Test object
echo json_encode($myResults, JSON_THROW_ON_ERROR + JSON_PRETTY_PRINT, 512);
