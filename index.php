<?php

define(WS_BOMB, -1);
define(WS_UNSET, '');

function generate_grid($width=9, $height=9, $density=5.5) {
    srand($_GET['gameid']);
    $bombs = round($width*$height/$density);
    $field = array_fill(0, $width, array_fill(0, $height, WS_UNSET));
    for ($n=0; $n<$bombs; $n++) {
        while (WS_BOMB == $field[$rw = rand(0, $width - 1)][$rh = rand(0, $height - 1)])
            ; # already a bomb, retry
        $field[$rw][$rh] = WS_BOMB;
    }
    return $field;
}

// -1,-1   0,-1   1,-1
// -1, 0          1, 0
// -1, 1   0, 1   1, 1
function count_adjacent_bombs($field, $x, $y) {
    $count = 0;
    $ox=-1;$oy=-1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox= 0;$oy=-1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox= 1;$oy=-1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox=-1;$oy= 0; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox= 1;$oy= 0; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox=-1;$oy= 1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox= 0;$oy= 1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    $ox= 1;$oy= 1; if (($x+$ox >= 0) && ($y+$oy >= 0) && (WS_BOMB == $field[$x+$ox][$y+$oy])) $count++;
    return $count;
}

function generate_html_table($grid) {
    echo "<table>\n";
    for ($x=0; $x<count($grid[0]); $x++) {
        echo "  <tr>\n";
        for ($y=0; $y<count($grid); $y++) {
            $adj_bombs = count_adjacent_bombs($grid, $x, $y);
            $contents = WS_BOMB == $grid[$x][$y] ? 'x' : $adj_bombs;
            $html_class = WS_BOMB == $grid[$x][$y] ? 'bomb' : "count-$adj_bombs";
            $target = WS_BOMB == $grid[$x][$y] ? '#new' : "#x$x-y$y";
            echo "    <td class=\"$html_class\"><a class=\"info\" href=\"$target\"><span>".$contents."</span></a>
                      <a class=\"flag\" title=\"Flag the box\" href=\"#flag-$x-$y\"><span>flag</span></a></td>\n";
        }
        echo "  </tr>\n";
    }
    echo "</table>\n";
}

$redirect = 0;
if (!(($width = $_GET['w']) && ($height = $_GET['h']))) {
    $width = 9;
    $height = 9;
    $redirect = 1;
}
if (!($gameid = $_GET['gameid'])) {
    srand((double)microtime()*1000000);
    $gameid = rand();
    $redirect = 1;
}
if ($redirect) {
    header("Location: ?w=$width&h=$height&gameid=$gameid");
} else {
    $grid = generate_grid($width, $height);
    ?><!DOCTYPE html>
<html>
<link rel="stylesheet" href="style.css" />
<!--[if lte IE 8]>
<script src="html5-ie.js" type="text/javascript"></script>
<![endif]-->
<title>Minesweeper</title>
<article>
<h1>Minesweeper</h1>
<ul id="menu">
  <li>Game
    <ul><li><a href="?w=9&amp;h=9">Beginner</a></li>
    <li><a href="?w=16&amp;h=16">Intermediate</a></li>
    <li><a href="?w=30&amp;h=16">Expert</a></li></ul>
  </li>
  <li>Help
    <ul><li><a href="http://da.weeno.net/blog/?post/2010/01/29/Comment-miner-son-apr%C3%A8s-midi">Presentation en francais</a></li></ul>
  </li>
</ul>
<?php
    echo "<a title=\"Start new game\" id=\"new\" href=\"?w=$width&amp;h=$height&amp;gameid=".rand()."\"><span>Start new game</span></a>\n";
    generate_html_table($grid);
    echo "</article>\n</html>";
}
?>
