<?php
// Custom error handler to display errors clearly
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr - Error on line $errline in file $errfile<br>";
    // Don't execute PHP internal error handler
    return true;
}

// Custom exception handler
function customExceptionHandler($exception) {
    echo "<b>Exception:</b> " . $exception->getMessage() . "<br>";
}

// Register handlers
set_error_handler("customErrorHandler");
set_exception_handler("customExceptionHandler");

// Function to safely include files with error check
function safeInclude($file) {
    if (file_exists($file)) {
        include $file;
    } else {
        die("File not found: " . htmlspecialchars($file));
    }
}
?>
