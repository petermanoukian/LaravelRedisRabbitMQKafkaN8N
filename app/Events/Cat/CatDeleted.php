<?php
namespace App\Events\Cat;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class CatDeleted
{
    use Dispatchable, InteractsWithSockets;

    public int $id;
    public string $name;
    public string $des;

    public function __construct(int $id, string $name, string $des)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->des  = $des;
    }
}

