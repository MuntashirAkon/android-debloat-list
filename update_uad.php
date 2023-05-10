<?php
/* SPDX-License-Identifier: AGPL-3.0-or-later */

const LAST_COMMIT = "11f27c671cba278d71296cdef4c5a5dba06add5e";
const THIS_COMMIT = "11f27c671cba278d71296cdef4c5a5dba06add5e";

const COLOR_RED = 31;
const COLOR_GREEN = 32;

function get_link(string $commit_hash): string {
    return "https://raw.githubusercontent.com/0x192/universal-android-debloater/$commit_hash/resources/assets/uad_lists.json";
}

$old_list_link = get_link(LAST_COMMIT);
$new_list_link = get_link(THIS_COMMIT);

if ($old_list_link == $new_list_link) {
    echo "Already up-to-date.\n";
    exit(0);
}

$old_list = json_decode(file_get_contents($old_list_link), true);
$new_list = json_decode(file_get_contents($new_list_link), true);

// Iterate over the new list to find changes w.r.t old list. Delete the matched item from the old list
foreach ($new_list as $item) {
    if ($item['removal'] == 'Unsafe') {
        // Exclude Unsafe items
        continue;
    }
    $old_item = find_in_old_list($item['id']);
    if ($item != $old_item) {
        // Two arrays aren't the same, check one by one and print values
        print(" {\n");
        foreach ($item as $key => $value) {
            if ($value != $old_item[$key]) {
                // These values aren't the same
                // Print diff
                print_diff($key, $old_item[$key], COLOR_RED);
                print_diff($key, $value, COLOR_GREEN);
            } else {
                // Values are the same, print as is
                print_diff($key, $value, null);
            }
        }
        print(" }\n");
    }
}

// The remaining items in the old list are removed
foreach ($old_list as $item) {
    if ($item['removal'] == 'Unsafe') {
        // Exclude Unsafe items
        continue;
    }
    // List this item as removed item.
    print("\e[31m-{\e[0m\n");
    foreach ($item as $key => $value) {
        print_diff($key, $value, null);
    }
    print("\e[31m-}\e[0m\n");
}

exit(0);

function print_diff(string $key, string|array|null $value, ?int $color): void {
    if ($color == COLOR_RED) {
        $symbol = '-';
        $color_begin = "\e[31m";
        $color_end = "\e[0m";
    } else if ($color == COLOR_GREEN) {
        $symbol = '+';
        $color_begin = "\e[32m";
        $color_end = "\e[0m";
    } else {
        $symbol = ' ';
        $color_begin = '';
        $color_end = '';
    }
    if ($value == null) {
        printf("$symbol$color_begin  $key: null,$color_end");
    } else if (is_string($value)) {
        printf("$symbol$color_begin  $key: $value,$color_end");
    } else if (is_array($value)) {
        printf("$symbol$color_begin  $key: [$color_end");
        foreach ($value as $item) {
            printf("$symbol$color_begin    $item,$color_end");
        }
        printf("$symbol$color_begin  ],$color_end");
    }
}

function find_in_old_list(string $id): ?array {
    global $old_list;
    $c = count($old_list);
    for ($i = 0; $i < $c; ++$i) {
        $item = $old_list[$i];
        if ($item['id'] == $id) {
            array_splice($old_list, $i, 1);
            return $item;
        }
    }
    return null;
}

function get_removal(string $uad_removal): string {
    switch ($uad_removal) {
        default:
        case "Recommended":
            return "delete";
        case "Advanced":
            return "replace";
        case "Expert":
            return "caution";
        case "Unsafe":
            return "unsafe";
    }
}
