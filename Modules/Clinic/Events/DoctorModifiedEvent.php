<?php

namespace Modules\Clinic\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DoctorModifiedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $doctor;
    public $action;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($doctor, $action)
    {
        $this->doctor = $doctor;
        $this->action = $action;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
