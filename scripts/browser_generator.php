<?php


const REPO_DIR = __DIR__ . "/..";
const BROWSER_DIR = REPO_DIR . "/browser";
const SRC_DIR = BROWSER_DIR . "/src";
const BLOATWARE_DIR = SRC_DIR . "/bloatware";
const SUMMARY_FILE = SRC_DIR . "/SUMMARY.md";
const SITEMAP_FILE = SRC_DIR . "/sitemap.xml";

# Create bloatware list
$bloatware_list = [];
@mkdir(BLOATWARE_DIR,0777, true);
foreach (scandir(REPO_DIR) as $filename) {
    if (!str_ends_with($filename, ".json")) {
        continue;
    }
    $file = REPO_DIR . '/' . $filename;
    $type = substr($filename, 0, -5);
    try {
        $list = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        continue;
    }
    foreach ($list as $item) {
        $id = $item['id'];
        $name = $item['label'] ?? $id;
        $bloatware_list[$id] = $name;
        $content = create_bloatware_item($type, $item);
        file_put_contents(BLOATWARE_DIR . "/$id.md", $content);
    }
}
asort($bloatware_list);

# Copy readme
copy(BROWSER_DIR . "/README.md", SRC_DIR . "/README.md");

# Create summary
$summary = <<<EOF
# Summary

[Welcome!](README.md)

# Bloatware

EOF;
foreach ($bloatware_list as $id => $name) {
    $summary .= "- [". $name ."](bloatware/". $id .".md)\n";
}
file_put_contents(SUMMARY_FILE, $summary);

# Create sitemap
$SITE_NAME = "https://muntashirakon.github.io/android-debloat-list";
$urls = '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
$urls .= <<<EOF
<url><loc>{$SITE_NAME}</loc></url>
EOF;
foreach ($bloatware_list as $id => $name) {
    $urls .= <<<EOF
<url><loc>$SITE_NAME/bloatware/{$id}.html</loc></url>
EOF;
}
$urls .= '</urlset>';
file_put_contents(SITEMAP_FILE, $urls);

exit(0);

// Functions //

function create_bloatware_item(string $type, array $item): string {
    $id = $item["id"];
    $name = $item['label'] ?? $id;
    $description = str_replace("\n", "\n\n", trim($item["description"]));
    $warning = isset($item['warning']) ? <<<EOF
<div class="warning">
{$item['warning']}
</div>
EOF : "";
    $removal = $item['removal'];
    $removal_name = removal_to_string($item['removal']);
    $type_tag = type_to_string($type);
    $web = "";
    if (isset($item["web"])) {
        $web = "## References { .refs }\n";
        foreach ($item["web"] as $num => $link) {
            $n = $num + 1;
            $web .= "{$n}. <{$link}>\n";
        }
    }

    return <<<EOF
# {$name}

`{$id}`

<div class="tags">
<ul>
  <li data-tag={$type}>{$type_tag}</li>
  <li data-tag={$removal}>{$removal_name}</li>
</ul>
</div>

{$warning}

{$description}

{$web}

<a href="app-manager://details?id={$id}">Open in App Manager</a>
EOF;
}


function type_to_string(string $type): string {
    switch ($type) {
        case "aosp": return '<i class="fa fa-android"></i> AOSP';
        case "carrier": return '<i class="fa fa-signal"></i> Carrier';
        case "google": return '<i class="fa fa-google"></i> Google';
        case "misc": return "Others";
        case "oem": return '<i class="fa fa-microchip"></i> OEM';
        case "pending": return "Pending";
        default: throw new Exception("Invalid type: $type");
    }
}


function removal_to_string(string $removal_name): string {
    switch ($removal_name) {
        case "delete": return "Safe to delete";
        case "replace": return "Replace with alternative";
        case "caution": return "Exercise caution";
        case "unsafe": return "Unsafe";
        default: throw new Exception("Invaid removal: $removal_name");
    }
}
