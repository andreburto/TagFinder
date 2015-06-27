<?php

include_once("tag-finder.php");
include_once("tag-fixer.php");

function check_test($val1, $val2, $msg) {
    global $count;
    $retval = null;
    try {
        if ($val1 !== $val2) { throw new Exception($msg); }
        printf("Success %s.\n", $count);
        $retval = true;
    } catch (Exception $ex) {
        printf("Fail %s: %s.\n", $count, $ex->getMessage());
        $retval = false;
    }
    $count++;
    return $retval;
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

// Fix root domains
function test5() {
    check_test(fixLinkDomain("/", "andrewburton.biz"),
               "http://andrewburton.biz/",
               "Failed to fix root domain without protocol.");
}
function test6() {
    return check_test(fixLinkDomain("/", "https://andrewburton.biz"),
                      "https://andrewburton.biz/",
                      "Failed to fix root domain with HTTPS protocol.");
}
function test7() {
    return check_test(fixLinkDomain("/", "http://andrewburton.biz"),
                      "http://andrewburton.biz/",
                      "Failed to fix root domain with HTTP protocol.");
}
function test8() {
    global $doc, $html;
    $a = findSpecificTag($doc, 'a', 'B');
    $url = strval($a[0]['ATTR'][0]['val']);
    return check_test(fixLinkDomain($url, "andrewburton.biz", "https"),
                      "https://andrewburton.biz/",
                      "Failed to fix root domain with found link.");
}

// Go up a level
function test9() {
    return check_test(fixLinkDomain("../img/pics.jpg", "andrewburton.biz", "https"),
                      "https://andrewburton.biz/img/pics.jpg",
                      "Failed to fix domain with ../img/pics.jpg at the start.");    
}

// Variables
$count = 1;
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
<p><a href="/" title='Home page'>Andy</a> was here.</p>
</body>
</html>
EOL;

// Start
printf("Test began: %s\n", date("Y-m-d H:i:s"));

// TEST SET 1
printf("Test TagFinder\n");
// Perform TagFinder tests
test1();
test2();
test3();
test4();

// TEST SET 2
printf("Test TagFixer\n");
// Perform TagFixer tests
test5();
test6();
test7();
test8();
test9();

?>