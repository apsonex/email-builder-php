<?php

namespace Apsonex\EmailBuilderPhp\Support;

use Illuminate\Support\Str;
use Apsonex\EmailBuilderPhp\Concerns\Makebale;

class Industries
{
    use Makebale;


    /**
     * Get all industry slugs mapped to their labels.
     *
     * @return array<string, string> Array where keys are industry slugs and values are industry labels.
     * e.g. `["car-detailing" => "Car Detailing", ...]`
     */
    public function all(): array
    {
        $content = [];

        $path = email_builder_php_data_path('email-subjects');

        foreach (glob($path . '/*.json') as $file) {
            $json = json_decode(file_get_contents($file), true);
            if (!empty($json['industry']['slug']) && !empty($json['industry']['label'])) {
                $content[$json['industry']['slug']] = $json['industry']['label'];
            }
        }

        return $content;
    }


    /**
     * Get the industry data array for the given industry.
     *
     * @param string $industry Industry slug/name
     *
     * @return array<string, mixed>|null Returns an array with the structure:
     * [
     *   "industry" => [ "slug" => string, "label" => string ],
     *   "subjects" => [
     *       "<industry-slug>" => [
     *           "category" => [ "label" => string, "slug" => string ],
     *           "items" => [
     *               "<subject-slug>" => string, // subject label
     *               // ...
     *           ],
     *       ],
     *       // ...
     *   ],
     * ]
     * or null if file not found.
     */
    public function industry(string $industry): ?array
    {
        $file = email_builder_php_data_path('email-subjects') . '/' . strtolower(str_replace(' ', '-', $industry)) . '.json';

        return is_file($file) ? json_decode(file_get_contents($file), true) : null;
    }
}
