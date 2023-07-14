<?php

$json_files = array_filter(scandir(__DIR__), function ($item) {
    return str_ends_with($item, ".json");
});

foreach ($json_files as $json_file) {
    $list = json_decode(file_get_contents(__DIR__ . 'test.php/' . $json_file), true);
    usort($list, function ($o1, $o2) {
        return $o1['id'] <=> $o2['id'];
    });
    file_put_contents(__DIR__ . 'test.php/' . $json_file, json_encode($list, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
