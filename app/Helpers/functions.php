<?php
// Small validation helper
function require_fields(array $data, array $fields): array
{
    $missing = [];
    foreach ($fields as $f) {
        if (!array_key_exists($f, $data) || $data[$f] === '' || $data[$f] === null) {
            $missing[] = $f;
        }
    }
    return $missing;
}
