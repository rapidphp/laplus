<?php

namespace Rapid\Laplus\Present\Generate\Testing;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasTestingPresent;
use Rapid\Laplus\Present\Present;

class AnonymousTestingPresentableModel extends Model
{
    use HasTestingPresent;

    public function __construct(
        ?string         $table = null,
        public ?Closure $callback = null,
    )
    {
        parent::__construct();
        $this->table = $table;
    }

    protected function present(Present $present)
    {
        if (isset($this->callback)) {
            ($this->callback)($present);
        }
    }
}