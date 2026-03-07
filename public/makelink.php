<?php
// TEMPORARY — delete after use
$target = '/home/fmsport/archery/public';
$link   = '/home/fmsport/public_html/_wildcard_.fmsport.biz';

if (is_link($link)) {
    echo "Symlink already exists: $link -> " . readlink($link);
} elseif (is_dir($link)) {
    // Remove the empty directory cPanel created
    if (@rmdir($link)) {
        if (symlink($target, $link)) {
            echo "Done! Symlink created: $link -> $target";
        } else {
            echo "rmdir OK but symlink failed.";
        }
    } else {
        echo "Cannot remove directory (not empty?). Files inside:<br>";
        foreach (scandir($link) as $f) echo "$f<br>";
    }
} else {
    if (symlink($target, $link)) {
        echo "Done! Symlink created: $link -> $target";
    } else {
        echo "Failed to create symlink. Check paths exist.";
    }
}
