<?php
/* SPDX-License-Identifier: AGPL-3.0-or-later */

const LAST_COMMIT = "246c2208b08ebfbe31e0a0eb90430fada932c703";
const THIS_COMMIT = "a04104630ec06b2c85e196725c704d0c2a03c5c0";
const REPO_DIR = __DIR__ . "/..";
const LINT_DIR = REPO_DIR . "/build";

const COLOR_RED = 31;
const COLOR_GREEN = 32;

function get_link(string $commit_hash): string {
    return "https://raw.githubusercontent.com/Universal-Debloater-Alliance/universal-android-debloater-next-generation/$commit_hash/resources/assets/uad_lists.json";
}

function convert_to_old(array $list): array {
    // New array use "id" as key, need to replace it with "id" again
    $new_list = [];
    foreach($list as $id => $item) {
        $item['id'] = $id;
        $new_list[] = $item;
    }
    return $new_list;
}

$old_list_link = get_link(LAST_COMMIT);
$new_list_link = get_link(THIS_COMMIT);

$old_list = convert_to_old(json_decode(file_get_contents($old_list_link), true));
$new_list = convert_to_old(json_decode(file_get_contents($new_list_link), true));
$id_list = explode("\n", file_get_contents(LINT_DIR . '/ids.txt'));

$new_items = [
    "aosp" => [],
    "carrier" => [],
    "google" => [],
    "misc"=> [],
    "oem"=> [],
    "pending"=> [],
];
// Iterate over the new list to find changes w.r.t old list. Delete the matched item from the old list
foreach ($new_list as $item) {
    $old_item = find_in_old_list($item['id']);
    if (!in_array($item['id'], $id_list)) {
        // This is a new item
        // print("\e[32m+{\e[0m\n");
        // foreach ($item as $key => $value) {
        //     print_diff($key, $value, COLOR_GREEN);
        // }
        // print("\e[32m+}\e[0m\n");
        $new_items[strtolower($item['list'])][] = $item;
        continue;
    }
    if ($old_item != null && $item != $old_item) {
        // Two arrays aren't the same, check one by one and print values
        print(" {\n");
        foreach ($item as $key => $value) {
            if (!isset($old_item[$key])) {
                // New item
                print_diff($key, $value, COLOR_GREEN);
            } else if ($value != $old_item[$key]) {
                // These values aren't the same
                // Print diff
                print_diff($key, $old_item[$key], COLOR_RED);
                print_diff($key, $value, COLOR_GREEN);
            } else {
                // Values are the same, print as is
                print_diff($key, $value, null);
            }
        }
        // Print any outstanding items
        foreach ($old_item as $key => $value) {
            if (!isset($item[$key])) {
                print_diff($key, $value, COLOR_RED);
            }
        }
        print(" }\n");
    }
}

// The remaining items in the old list are removed
foreach ($old_list as $item) {
    // List this item as removed item.
    print("\e[31m-{\e[0m\n");
    foreach ($item as $key => $value) {
        print_diff($key, $value, COLOR_RED);
    }
    print("\e[31m-}\e[0m\n");
}

// Update list with new items
foreach (scandir(REPO_DIR) as $filename) {
    if (!str_ends_with($filename, ".json")) {
        continue;
    }
    $type = substr($filename, 0, -5);
    // Load old items
    $file = REPO_DIR . '/' . $filename;
    try {
        $list = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fprintf(STDERR, "Unable to parse %s: %s\n", $filename, $e->getMessage());
        continue;
    }
    // Add new items
    foreach ($new_items[$type] as $item) {
        $list[] = get_adl_formatted_item($item);
    }
    // Sort items
    usort($list, function ($o1, $o2) {
        return $o1['id'] <=> $o2['id'];
    });
    file_put_contents($file, json_encode($list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
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
        printf("$symbol$color_begin  \"$key\": null,$color_end");
    } else if (is_string($value)) {
        $value = str_replace("\n", "\\n", $value);
        print("$symbol$color_begin  \"$key\": \"$value\",$color_end");
    } else if (is_array($value)) {
        printf("$symbol$color_begin  \"$key\": [$color_end\n");
        foreach ($value as $item) {
            printf("$symbol$color_begin    \"$item\",$color_end\n");
        }
        printf("$symbol$color_begin  ],$color_end");
    }
    printf("\n");
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
            fprintf(STDERR, "Warning: Invalid removal: " . $uad_removal . "\n");
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

function get_adl_formatted_item(array $uad_item): array {
    $id = $uad_item["id"];
    $description = $uad_item["description"];
    $dependencies = $uad_item["dependencies"];
    $required_by = $uad_item["neededBy"];
    $removal = get_removal($uad_item["removal"]);
    $item = [];
    $item["id"] = $id;
    $item["description"] = $description;
    if (!empty($dependencies) && count($dependencies) > 0) {
        $item["dependencies"] = $dependencies;
    }
    if (!empty($required_by) && count($required_by) > 0) {
        $item["required_by"] = $required_by;
    }
    $item['removal'] = $removal;
    return $item;
}
