<?php

include_once("tag-finder.php");

function check_test($val1, $val2, $msg) {
    global $count;
    try {
        if ($val1 !== $val2) { throw new Exception($msg); }
        printf("Success %s.\n", $count);
        return true;
    } catch (Exception $ex) {
        printf("Fail %s: %s.\n", $count, $ex->getMessage());
        return false;
    }
}

// See if findNodes returns an array
function test1() {
    global $html;
    $nodes = findNodes($html);
    return check_test(is_array($nodes), true, "findNodes could not parse nodes");
}

// See if findNodes returns an array with anything in it
function test2() {
    global $html;
    $nodes = findNodes($html);
    $hasLength = (count($nodes)>0?true:false);
    return check_test($hasLength, true, "findNodes could not find nodes");
}

// See if parseHtml return an array
function test3() {
    global $doc, $html;
    $doc = parseHtml($html);
    return check_test(is_array($doc), true, "parseHtml could not fully parse html");
}

// Count all the p tags
function test4() {
    global $doc;
    $p = findSpecificTag($doc, 'p');
    $countP = count($p);
    return check_test($countP, 10, "Did not find 10 p tags");
}

// Variables
$doc = null;
$html=<<<EOL
<!DOCTYPE html>
<html>
<head><title>K-Thing</title></head>
<body>
<p>Hello, world!</p>
<p>Goodbye.</p>
<p>5 > 1</p>
<p>5 < 1</p>
<hr width="300" />
<p><a href="http://andrewburton.biz" title='Home page'>Andy</a> was here.</p>
</body>
</html>
EOL;

// Start
printf("Test began: %s\n", date("Y-m-d H:i:s"));

// TEST SET 1
$count = 0;
printf("Test TagFinder\n");
// Perform tests
while(true) {
    $count++;
    $f = sprintf("test%s", $count);
    if (function_exists($f) == false) { break; }
    call_user_func($f);
}

?>