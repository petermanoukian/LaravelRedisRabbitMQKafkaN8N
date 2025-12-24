<?php
namespace App\Events\Prod;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ProdDeleted
{
    use Dispatchable, InteractsWithSockets;

    public int $id;
    public string $name;
    public string $des;

    /**
     * Create a new event instance.
     */
    public function __construct(int $id, string $name, string $des)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->des  = $des;
    }
}
