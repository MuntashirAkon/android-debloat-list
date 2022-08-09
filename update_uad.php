<?php
/* SPDX-License-Identifier: GPL-3.0-or-later */

const LIST_JSON = 'https://raw.githubusercontent.com/0x192/universal-android-debloater/master/resources/assets/uad_lists.json';

$list = json_decode(file_get_contents(LIST_JSON), true);

$new_list = array();

foreach ($list as $item) {
    $new_item = array();
    $new_item['id'] = $item['id'];
    $new_item['description'] = $item['description'];
    $new_item['removal'] = get_removal($item['removal']);
    if ($item['dependencies'] != null) {
        $new_item['dependencies'] = $item['dependencies'];
    }
    if ($item['neededBy'] != null) {
        $new_item['required_by'] = $item['neededBy'];
    }
    $list_type = strtolower($item['list']);
    if (!isset($new_list[$list_type])) {
        $new_list[$list_type] = array();
    }
    // For now, exclude `unsafe` items
    if ($new_item['removal'] != 'unsafe') {
        $new_list[$list_type][] = $new_item;
    }
}

# Save to dir
foreach ($new_list as $list_type => $list) {
    file_put_contents(__DIR__ . '/' . $list_type . '.json', json_encode($list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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