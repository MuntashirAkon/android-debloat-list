<?php
/* SPDX-License-Identifier: AGPL-3.0-or-later */

const SUPPORTED_REMOVAL_TYPES = ['delete', 'replace', 'caution', 'unsafe'];
const SUPPORTED_TAGS = [];
const SUPPRESS_LINT_CONST_LABEL_SAME_AS_ID = 'LabelSameAsId';
const SUPPRESS_LINT_CONSTS = [SUPPRESS_LINT_CONST_LABEL_SAME_AS_ID];
const REPO_DIR = __DIR__ . "/..";
const SUGGESTIONS_DIR = REPO_DIR . '/suggestions';
const LINT_DIR = __DIR__ . "/../build";

if (!file_exists(LINT_DIR)) {
    mkdir(LINT_DIR, 0777, true);
}

$lint_writer = fopen(LINT_DIR . "/lint-results.txt", "w");

// START MAIN

$error_count = 0;
foreach (scandir(REPO_DIR) as $filename) {
    if (!str_ends_with($filename, ".json")) {
        continue;
    }
    $file = REPO_DIR . '/' . $filename;
    $type = substr($filename, 0, -5);
    try {
        $list = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fprintf($lint_writer, "Unable to parse %s: %s\n", $filename, $e->getMessage());
        ++$error_count;
        continue;
    }
    fprintf($lint_writer, "Adding $filename\n");
    foreach ($list as $item) {
        $error_count += validate_bloatware_item($item);
    }
}

foreach (scandir(SUGGESTIONS_DIR) as $filename) {
    if (!str_ends_with($filename, ".json")) {
        continue;
    }
    $suggestion_file = SUGGESTIONS_DIR . '/' . $filename;
    $suggestion_id = substr($filename, 0, -5);
    try {
        $single_suggestion_list = json_decode(file_get_contents($suggestion_file), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fprintf($lint_writer, 'Unable to parse %s: %s', $filename, $e->getMessage());
        ++$error_count;
        continue;
    }
    fprintf($lint_writer, "Adding $filename\n");
    foreach ($single_suggestion_list as $suggestion) {
        $error_count += validate_suggestion_item($suggestion);
    }
}

if ($error_count != 0) {
    $msg = "\n$error_count ERRORS.\n";
    fprintf($lint_writer, $msg);
    fprintf(STDERR, $msg);
    exit(1);
} else {
    $msg = "No errors.\n";
    fprintf($lint_writer, $msg);
    fprintf(STDERR, $msg);
    exit(0);
}

// END MAIN

function validate_bloatware_item(array $item): int {
    global $lint_writer;
    $suppressed = isset($item['suppress']) ? parse_suppress($item['suppress']) : [];
    $error_count = 0;
    // `id` is a string
    if (gettype($item['id']) != 'string') {
        fprintf($lint_writer, "Expected `id` field to be a string, found: " . gettype($item['id']) . "\n");
        ++$error_count;
    }
    // `label` is an optional string
    if (isset($item['label'])) {
        if (gettype($item['label']) != 'string') {
            fprintf($lint_writer, "{$item['id']}: Expected `label` field to be a string, found: " . gettype($item['label']) . "\n");
            ++$error_count;
        } else if ($item['label'] == $item['id']) {
            if (!in_array(SUPPRESS_LINT_CONST_LABEL_SAME_AS_ID, $suppressed)) {
                fprintf(STDERR, "{$item['id']}: `label` is the same as ID.\n");
            }
        }
    } else {
        fprintf(STDERR, "{$item['id']}: Missing `label`\n");
    }
    // `dependencies` is an optional string[]
    if (isset($item['dependencies'])) {
        if (gettype($item['dependencies']) != 'array') {
            fprintf($lint_writer, "{$item['id']}: Expected `dependencies` field to be an array, found: " . gettype($item['dependencies']) . "\n");
            ++$error_count;
        } else {
            foreach ($item['dependencies'] as $dependency) {
                if (gettype($dependency) != 'string') {
                    fprintf($lint_writer, "{$item['id']}: Expected `dependencies` items to be a string, found: " . gettype($dependency) . "\n");
                    ++$error_count;
                }
            }
        }
    }
    // `required_by` is an optional string[]
    if (isset($item['required_by'])) {
        if (gettype($item['required_by']) != 'array') {
            fprintf($lint_writer, "{$item['id']}: Expected `required_by` field to be an array, found: " . gettype($item['required_by']) . "\n");
            ++$error_count;
        } else {
            foreach ($item['required_by'] as $required_by) {
                if (gettype($required_by) != 'string') {
                    fprintf($lint_writer, "{$item['id']}: Expected `required_by` items to be a string, found: " . gettype($required_by) . "\n");
                    ++$error_count;
                }
            }
        }
    }
    // `tags` is an optional string[]
    if (isset($item['tags'])) {
        if (gettype($item['tags']) != 'array') {
            fprintf($lint_writer, "{$item['id']}: Expected `tags` field to be an array, found: " . gettype($item['tags']) . "\n");
            ++$error_count;
        } else {
            foreach ($item['tags'] as $tag) {
                if (gettype($tag) != 'string') {
                    fprintf($lint_writer, "{$item['id']}: Expected `tags` items to be a string, found: " . gettype($tag) . "\n");
                    ++$error_count;
                } else if (!in_array($tag, SUPPORTED_TAGS)) {
                    fprintf($lint_writer, "{$item['id']}: Invalid `tag`: $tag\n");
                    ++$error_count;
                }
            }
        }
    }
    // `description` is a string
    if (gettype($item['description']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `description` field to be a string, found: " . gettype($item['description']) . "\n");
        ++$error_count;
    }
    // `web` is an optional string[]
    if (isset($item['web'])) {
        if (gettype($item['web']) != 'array') {
            fprintf($lint_writer, "{$item['id']}: Expected `web` field to be an array, found: " . gettype($item['web']) . "\n");
            ++$error_count;
        } else {
            foreach ($item['web'] as $site) {
                if (gettype($site) != 'string') {
                    fprintf($lint_writer, "{$item['id']}: Expected `web` items to be a string, found: " . gettype($site) . "\n");
                    ++$error_count;
                }
            }
        }
    }
    // `removal` is a string
    if (gettype($item['removal']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `removal` field to be a string, found: " . gettype($item['removal']) . "\n");
        ++$error_count;
    } else if (!in_array($item['removal'], SUPPORTED_REMOVAL_TYPES)) {
        fprintf($lint_writer, "{$item['id']}: Invalid `removal` type: {$item['removal']}\n");
        ++$error_count;
    }
    // `warning` is an optional string
    if (isset($item['warning']) && gettype($item['warning']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `warning` field to be a string, found: " . gettype($item['warning']) . "\n");
        ++$error_count;
    }
    // `suggestions` is an optional string
    if (isset($item['suggestions'])) {
        if (gettype($item['suggestions']) != 'string') {
            fprintf($lint_writer, "{$item['id']}: Expected `suggestions` field to be a string, found: " . gettype($item['suggestions']) . "\n");
            ++$error_count;
        } else {
            $suggestion_file = SUGGESTIONS_DIR . '/' . $item['suggestions'] . '.json';
            if (!file_exists($suggestion_file)) {
                fprintf($lint_writer, "{$item['id']}: Suggestion ID ({$item['suggestions']}) does not exist.\n");
                ++$error_count;
            }
        }
    }
    return $error_count;
}

function validate_suggestion_item(array $item): int {
    global $lint_writer;
    $error_count = 0;
    // `id` is a string
    if (gettype($item['id']) != 'string') {
        fprintf($lint_writer, "Expected `id` field to be a string, found: " . gettype($item['id']) . "\n");
        ++$error_count;
    }
    // `label` is a string
    if (gettype($item['label']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `label` field to be a string, found: " . gettype($item['label']) . "\n");
        ++$error_count;
    }
    // `reason` is an optional string
    if (isset($item['reason']) && gettype($item['reason']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `reason` field to be a string, found: " . gettype($item['reason']) . "\n");
        ++$error_count;
    }
    // `source` is an optional string
    if (isset($item['source'])) {
        if (gettype($item['source']) != 'string') {
            fprintf($lint_writer, "{$item['id']}: Expected `source` field to be a string, found: " . gettype($item['source']) . "\n");
            ++$error_count;
        } else if (!preg_match("/^[fgas]+$/", $item['source'])) {
            fprintf($lint_writer, "{$item['id']}: Expected `source` field to contain one or more from `fgas`, found: {$item['source']}\n");
            ++$error_count;
        }
    }
    // `repo` is a string
    if (gettype($item['repo']) != 'string') {
        fprintf($lint_writer, "{$item['id']}: Expected `repo` field to be a string, found: " . gettype($item['repo']) . "\n");
        ++$error_count;
    }
    return $error_count;
}

function parse_suppress(?string $str): array {
    if ($str == null) return [];
    return preg_split("/,\\s*/", $str);
}