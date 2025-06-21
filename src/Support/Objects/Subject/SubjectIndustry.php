<?php

namespace Apsonex\EmailBuilderPhp\Support\Objects\Subject;

use Illuminate\Contracts\Support\Arrayable;

class SubjectIndustry implements Arrayable
{
    /** @var string */
    public string $slug;

    /** @var string */
    public string $label;

    /** @var SubjectCategory[] */
    public array $subjects;

    public function __construct(string $slug, string $label, array $subjects)
    {
        $this->slug = $slug;
        $this->label = $label;
        $this->subjects = $subjects;
    }

    public static function fromArray(array $data): self
    {
        $subjects = [];
        foreach ($data['subjects'] as $subject) {
            $subjects[] = SubjectCategory::fromArray($subject);
        }
        return new self(
            slug: $data['industry']['slug'],
            label: $data['industry']['label'],
            subjects: $subjects,
        );
    }

    public function toArray(): array
    {
        return [
            'slug' => $this->slug,
            'label' => $this->label,
            'subjects' => array_map(fn($s) => $s->toArray(), $this->subjects),
        ];
    }
}
