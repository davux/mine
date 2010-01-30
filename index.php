<?php

define(WS_BOMB, -1);
define(WS_UNSET, '');

function generate_grid($width=9, $height=9, $density=5.5) {
    srand($_GET['gameid']);
    $bombs = round($width*$height/$density);
    $field = array_fill(0, $height, array_fill(0, $width, WS_UNSET));
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
            $target = WS_BOMB == $grid[$x][$y] ? '#bombed' : "#x$x-y$y";
            echo "    <td class=\"$html_class\"><a class=\"info\" href=\"$target\"><span>".$contents."</span></a>
                      <a class=\"flag\" href=\"#flag-$x-$y\"><span>flag</span></a></td>\n";
        }
        echo "  </tr>\n";
    }
    echo "</table>\n";
}

?><!DOCTYPE html>
<html>
<link rel="stylesheet" href="style.css" />
<?php
echo "<a id=\"new\" href=\"?w=9&h=9&gameid=".rand()."\"><span>Start new game</span></a>\n";
if ($_GET['w'] && $_GET['h']) {
    $width = $_GET['w'];
    $height = $_GET['h'];
    $grid = generate_grid($width, $height);
    generate_html_table($grid);
}
srand((double)microtime()*1000000);
echo "</html>";
?>

