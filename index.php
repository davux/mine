<?php

define(WS_BOMB, -1);
define(WS_UNSET, '');

function generate_grid($width=9, $height=9, $density=5.5) {
    srand($_GET['gameid']);
    $bombs = round($width*$height/$density);
    $field = array_fill(0, $width, array_fill(0, $height, WS_UNSET));
    for ($n=0; $n<$bombs; $n++) {
        while (WS_BOMB == $field[$rw = rand(0, $width - 1)][$rh = rand(0, $height - 1)])
            ; # already a bomb, retry. For a low proportion of bombs, this method is fast.
        $field[$rw][$rh] = WS_BOMB;
    }
    return $field;
}

function neighbours($w, $h, $max_w, $max_h) {
    $result = array();
    if (($w >= 1) && ($h >= 1))
        $result[] = array($w-1, $h-1);
    if ($h >= 1)
        $result[] = array($w, $h-1);
    if (($w <= $max_w-1) && ($h >= 1))
        $result[] = array($w+1, $h-1);
    if ($w >= 1)
        $result[] = array($w-1, $h);
    if ($w <= $max_w-1)
        $result[] = array($w+1, $h);
    if (($w >= 1) && ($h <= $max_h-1))
        $result[] = array($w-1, $h+1);
    if ($h <= $max_h-1)
        $result[] = array($w, $h+1);
    if (($w <= $max_w-1) && ($h <= $max_h-1))
        $result[] = array($w+1, $h+1);
    return $result;
}

function mark_empty_box($w, $h, $tag, &$grid) {
    $max_w = count($grid) - 1;
    $max_h = count($grid[0]) - 1;
    $grid[$w][$h] = $tag;
    foreach (neighbours($w, $h, $max_w, $max_h) as $neighbour) {
        $toto = $grid[$neighbour[0]][$neighbour[1]];
        if (0 === $toto) {
            mark_empty_box($neighbour[0], $neighbour[1], $tag, $grid);
        }
    }
}

function count_adjacent_bombs($grid, $w, $h) {
    $count = 0;
    $wmax = count($grid);
    $hmax = count($grid[0]);
    foreach (neighbours($w, $h, $wmax, $hmax) as $neighbour)
        if (WS_BOMB == $grid[$neighbour[0]][$neighbour[1]])
            $count++;
    return $count;
}

function fill_numbers(&$grid) {
    for ($h=0; $h<count($grid[0]); $h++) {
        for ($w=0; $w<count($grid); $w++) {
            if (WS_BOMB != $grid[$w][$h]) {
                $grid[$w][$h] = count_adjacent_bombs($grid, $w, $h);
            }
        }
    }
    for ($h=0; $h<count($grid[0]); $h++) {
        for ($w=0; $w<count($grid); $w++) {
            if (0 === $grid[$w][$h]) {
                mark_empty_box($w, $h, "blank-$w-$h", $grid);
            }
        }
    }
}

function generate_html_table($grid) {
    echo "<table>\n";
    for ($h=0; $h<count($grid[0]); $h++) {
        echo "  <tr>\n";
        for ($w=0; $w<count($grid); $w++) {
            $value = $grid[$w][$h];
            if (substr($value, 0, 6) == 'blank-') {
                $contents = '&nbsp;';
                $html_class = 'count-0';
                $target = $value;
            } elseif (WS_BOMB == $value) {
                $contents = 'x';
                $html_class = 'bomb';
                $target = 'new';
            } else { // normal number
                $contents = $value;
                $html_class = "count-$value";
                $target = "x$w-y$h";
            }
            echo "    <td class=\"$html_class\"><a class=\"info\" href=\"#$target\"><span>".$contents."</span></a>
                      <a class=\"flag\" title=\"Flag the box\" href=\"#flag-$w-$h\"><span>flag</span></a></td>\n";
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
    fill_numbers($grid);
    ?><!DOCTYPE html>
<html>
<link rel="stylesheet" href="style.css" />
<!--[if lte IE 8]>
<script src="html5-ie.js" type="text/javascript"></script>
<![endif]-->
<title>Minesweeper, game #<?php echo "$gameid ($width x $height)" ?></title>
<article>
<h1>Minesweeper</h1>
<ul id="menu">
  <li>Game
    <ul><li><a href="?w=9&amp;h=9">Beginner</a></li>
    <li><a href="?w=16&amp;h=16">Intermediate</a></li>
    <li><a href="?w=30&amp;h=16">Expert</a></li></ul>
  </li>
  <li>Help
    <ul><li><a href="http://da.weeno.net/blog/?post/2010/01/29/Comment-miner-son-apr%C3%A8s-midi">Presentation en francais (blog)</a></li>
    <li><a href="README">Presentation in English</a></li>
    <li><a href="TODO">Want to help?</a></li></ul>
  </li>
</ul>
<?php
    echo "<a title=\"Start new game\" id=\"new\" href=\"?w=$width&amp;h=$height&amp;gameid=".rand()."\"><span>Start new game</span></a>\n";
    generate_html_table($grid);
    echo "</article>\n</html>";
}
?>
