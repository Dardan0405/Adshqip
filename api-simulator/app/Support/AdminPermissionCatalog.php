<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminPermissionCatalog
{
    public static function sections(): array
    {
        $path = base_path('routes/Admin-Permission.txt');

        if (! is_file($path)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) ?: [];
        $sections = [];
        $sectionIndex = null;
        $groupIndex = null;
        $subgroupIndex = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_ends_with($line, ':')) {
                $label = trim(substr($line, 0, -1));

                if (str_starts_with($label, 'Tab ')) {
                    $sectionName = trim(substr($label, 4));
                    $sections[] = [
                        'label' => $sectionName,
                        'groups' => [],
                    ];
                    $sectionIndex = array_key_last($sections);
                    $groupIndex = null;
                    $subgroupIndex = null;
                    continue;
                }

                if ($sectionIndex === null) {
                    continue;
                }

                if (in_array(Str::lower($label), ['web/mobile', 'mobile', 'web', 'contextual'], true)) {
                    if ($groupIndex === null) {
                        continue;
                    }

                    $sections[$sectionIndex]['groups'][$groupIndex]['subgroups'][] = [
                        'label' => $label,
                        'permissions' => [],
                    ];
                    $subgroupIndex = array_key_last($sections[$sectionIndex]['groups'][$groupIndex]['subgroups']);
                    continue;
                }

                $sections[$sectionIndex]['groups'][] = [
                    'label' => $label,
                    'permissions' => [],
                    'subgroups' => [],
                ];
                $groupIndex = array_key_last($sections[$sectionIndex]['groups']);
                $subgroupIndex = null;
                continue;
            }

            if ($sectionIndex === null || $groupIndex === null) {
                if ($sectionIndex === null) {
                    continue;
                }

                $sections[$sectionIndex]['groups'][] = [
                    'label' => 'Permissions',
                    'permissions' => [],
                    'subgroups' => [],
                ];
                $groupIndex = array_key_last($sections[$sectionIndex]['groups']);
                $subgroupIndex = null;
            }

            $permissionLabel = trim($line, ", \t\n\r\0\x0B");
            $groupLabel = $sections[$sectionIndex]['groups'][$groupIndex]['label'];
            $scopeParts = [$sections[$sectionIndex]['label'], $groupLabel];

            if ($subgroupIndex !== null) {
                $scopeParts[] = $sections[$sectionIndex]['groups'][$groupIndex]['subgroups'][$subgroupIndex]['label'];
            }

            $scopeParts[] = $permissionLabel;

            $permission = [
                'key' => self::makeKey($scopeParts),
                'label' => $permissionLabel,
            ];

            if ($subgroupIndex !== null) {
                $sections[$sectionIndex]['groups'][$groupIndex]['subgroups'][$subgroupIndex]['permissions'][] = $permission;
            } else {
                $sections[$sectionIndex]['groups'][$groupIndex]['permissions'][] = $permission;
            }
        }

        return $sections;
    }

    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::sections() as $section) {
            foreach ($section['groups'] as $group) {
                foreach ($group['permissions'] as $permission) {
                    $keys[] = $permission['key'];
                }

                foreach ($group['subgroups'] as $subgroup) {
                    foreach ($subgroup['permissions'] as $permission) {
                        $keys[] = $permission['key'];
                    }
                }
            }
        }

        return $keys;
    }

    private static function makeKey(array $parts): string
    {
        return collect($parts)
            ->map(function (string $part) {
                $normalized = Str::lower(str_replace('&', 'and', $part));
                $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

                return trim($normalized, '_');
            })
            ->filter()
            ->implode('.');
    }
}
