<?php

namespace Apsonex\EmailBuilderPhp\Support\Blocks;

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Support\Data;
use Apsonex\EmailBuilderPhp\Concerns\Makebale;

class Prebuilt
{
    use Makebale;

    public function categories(): array
    {
        $file = Data::path('prebuilt-blocks/categories.json');

        return [
            'categories' => is_file($file)
                ? json_decode(file_get_contents($file), true)
                : [],
        ];
    }

    public function blocksByCategory(string $category): ?array
    {
        $category = Str::slug($category);

        $dir = Data::path('prebuilt-blocks/' . $category);

        if (!is_dir($dir)) {
            return null;
        }

        $items = [];

        foreach (scandir($dir) as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'json') {
                $json = json_decode(file_get_contents($path), true);
                if (!empty($json)) {
                    $items[] = $json;
                }
            }
        }

        return [
            'category' => $category,
            'items'    => $items,
        ];
    }
}
