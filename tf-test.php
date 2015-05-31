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

// Variables
$count = 0;
$html_test=<<<EOL
<!DOCTYPE html>
<html>
<head><title>K-Thing</title></head>
<body>
<p>Hello, world!</p>
<p>Goodbye.</p>
<p>5 > 1</p>
<p>5 < 1</p>
<hr width="300" />
<p><a href="http://andrewburton.biz">Andy</a> was here.</p>
</body>
</html>
EOL;

$doc = parseHtml($html_test);

print_r($doc);

$links = findSpecificTag($doc, 'p');

print_r($links);

$text = findText($doc);

print_r($text);

?>