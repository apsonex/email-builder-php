<?php
namespace Apsonex\EmailBuilderPhp\Concerns;

trait HasTableName
{
    protected ?string $table = null;

    public function tableName(string $tableName): static
    {
        $this->table = $tableName;

        return $this;
    }

}
