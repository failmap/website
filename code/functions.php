<?php

// error handling, make sure a mysql error makes the script exit non-zero
function myErrorHandler($errno, $errstr, $errfile, $errline) {
    fwrite(STDERR, "Died! Error($errno): {$errstr} on {$errfile}:{$errline}\n");
    exit(1);
}


// some helper-functions that should be put in classes...
// no exception on rounding, so if total is unknown or 0, just return 0.
function percentage($number, $total){
    if (!$total) return 0;
    return round(($number / $total) * 100, 0);
}

$ratingColors = array("0" => "000000", "F" => "ff0000", "T" => "ff0000", "D" => "ff0000",  "C" => "FFA500",  "B" => "FFA500", "A-" => "00ff00", "A" => "00ff00","A+" => "00ff00","A++" => "00ff00");
function getRatingColor($rating){
    global $ratingColors; // saves re-initializing the same list. But this should be a class thing.
    if (isset($ratingColors[$rating])){
        return $ratingColors[$rating];
    } else {
        return "AAAAAA";
    }
}

function makeHTMLId($text){
    return preg_replace("/[^a-zA-Z]+/", "", $text);
}