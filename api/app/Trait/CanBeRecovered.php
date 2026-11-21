<?php

namespace App\Trait;

trait CanBeRecovered
{
    public function recovery(): void
    {
        /** @var \Illuminate\Database\Eloquent\Model|null $this */
        if ($this->exists && $this->trashed()) $this->restore();
    }
}
