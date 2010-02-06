<?php

define(WS_BOMB, -1);
define(WS_UNSET, '');

function generate_grid($width=9, $height=9, $bombs) {
    srand($_GET['gameid']);
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
        foreach (explode(' ', $grid[$neighbour[0]][$neighbour[1]]) as $value) {;
            if ('0' === $value) {
                mark_empty_box($neighbour[0], $neighbour[1], $tag, $grid);
            } elseif (in_array($value, array(1,2,3,4,5,6,7,8))) {
                $grid[$neighbour[0]][$neighbour[1]] .= " beach-$tag";
            }
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
    echo "<form><table>\n";
    for ($h=0; $h<count($grid[0]); $h++) {
        echo "  <tr>\n";
        for ($w=0; $w<count($grid); $w++) {
            $additional = '';
            foreach (explode(' ', $grid[$w][$h]) as $value) {
                if (substr($value, 0, 6) == 'blank-') {
                    $contents = '&nbsp;';
                    $html_class = 'count count-0';
                    $target = $value;
                } elseif (WS_BOMB == $value) {
                    $contents = 'x';
                    $html_class = 'bomb';
                    $target = 'game';
                } elseif ('beach-' == substr($value, 0, 6)) {
                    $additional .= '<a class="beach" href="#'.substr($value,6).'">.</a>';
                } else { // normal number
                    $contents = $value;
                    $html_class = "count count-$value";
                    $target = "x$w-y$h";
                }
            }
            echo "    <td class=\"$html_class\">$additional<a class=\"info\" href=\"#$target\"><span>".$contents."</span></a><input type=\"checkbox\" /><span class=\"flag\"></span></td>\n";
        }
        echo "  </tr>\n";
    }
    echo "</table></form>\n";
}

$redirect = 0;
if (!(($width = $_GET['w']) && ($height = $_GET['h']))) {
    $width = 9;
    $height = 9;
    $_GET['n'] = 10;
    $redirect = 1;
}
if (!($gameid = $_GET['gameid'])) {
    srand((double)microtime()*1000000);
    $gameid = rand();
    $redirect = 1;
}
$bombs = ($_GET['n'] ? $_GET['n'] : round($width*$height/5.5));
if ($redirect) {
    header("Location: ?w=$width&h=$height&n=$bombs&gameid=$gameid");
} else {
    $grid = generate_grid($width, $height, $bombs);
    fill_numbers($grid);
    ?><!DOCTYPE html>
<html>
<link rel="shortcut icon" href="style/win95/img/mine.png" type="image/png" />
<link rel="stylesheet" href="style/base.css" />
<link rel="stylesheet" title="Windows 95" href="style/win95/win95.css" />
<link rel="stylesheet" title="Matrix (work in progress)" href="style/matrix/matrix.css" />
<!--[if lte IE 8]>
<script src="html5-ie.js" type="text/javascript"></script>
<![endif]-->
<title>Minesweeper, game #<?php echo "$gameid ($width x $height, $bombs mines)" ?></title>
<body id="top">

<article class="dialog" id="intro">
<header><a class="close button" href="#top"><span>OK...</span></a>
<h1>Introduction</h1></header>
<p><strong>This is a minesweeper</strong>. There are <?php echo $bombs;?> mines, you need to find them by clicking around... but not on the mines!</p>
<p>As an indication, the numbers tell you <strong>how many mines</strong> there are in the 8 adjacent cells.</p>
<p>When you guessed the location of a mine, click on the little white square to put a <strong>flag</strong> on the desired cell. The red counter tells
you how many mines are left to find.</p>
<p>To start a new game, either click on the smiley face or choose a difficulty level in the <em>Game</em> menu.</p>
<p><a href="#about">More information about the game...</a></p>
</article>

<article id="game">
<div id="timer"><div id="timer-1"></div><div id="timer-2"></div><div id="timer-3"></div></div>
<header>
<a title="Minesweeper" href="." rel="sidebar" class="minimize button"><span title="Bookmark CSS Minesweeper">Bookmark to play in the sidebar</span></a>
<h1>Minesweeper</h1>
</header>
<ul id="menu">
  <li>Game
    <ul><li><a href="?w=9&amp;h=9&amp;n=10">Beginner</a></li>
    <li><a href="?w=16&amp;h=16&amp;n=40">Intermediate</a></li>
    <li><a href="?w=30&amp;h=16&amp;n=99">Expert</a></li></ul>
  </li>
  <li>Help
    <ul>
      <li><a href="#intro">How to play?</a></li>
      <li><a href="http://da.weeno.net/blog/?post/2010/01/29/Comment-miner-son-apr%C3%A8s-midi">Presentation en francais (blog)</a></li>
      <li><a href="TODO">Want to help?</a></li>
      <li><a href="#about">About CSS Minesweeper...</a></li>
    </ul>
  </li>
</ul>
<?php
    echo "<a href=\"#game\" id=\"lose\"><span>Go to top.</span></a>";
    echo "<p id=\"controls\"><a accesskey=\"N\" title=\"Start new game\" id=\"new\" href=\"?w=$width&amp;h=$height&amp;n=$bombs&amp;gameid=".rand()."\"><span>Start <em>n</em>ew game</span></a></p>\n";
    generate_html_table($grid);
    echo "</article>\n";
    include('README.html');
    echo "</body>\n</html>";
}
?>
