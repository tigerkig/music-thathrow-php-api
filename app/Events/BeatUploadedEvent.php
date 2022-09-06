<?php

namespace App\Events;

use App\Models\Beat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class BeatUploadedEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;

    /**
     * @var Beat
     */
    public $beat;

    public function __construct(Beat $beat)
    {
        $this->beat = $beat;
    }
}
