<?php

namespace App\Events;

use App\Models\chatting\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $message;
    public function __construct(Message $message)
    {
        $this->message = $message->load('user', 'attachments');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id)
        ];
        return $channels;
    }
    public function broadcastAs()
    {
        return 'message.sent';
    }
    public function broadcastWith():array
    {
        $attachments = [];
        if ($this->message->attachments) {
            foreach ($this->message->attachments as $attachment) {
                $attachments[] = [
                    'id' => $attachment->id,
                    'message_id' => $attachment->message_id,
                    'user_id' => $attachment->user_id,
                    'file_name' => $attachment->file_name,
                    'file_type' => $attachment->file_type,
                    'file_size' => $attachment->file_size,
                    'file_url' => $attachment->file_url,
                    'full_url' => $attachment->full_url,
                ];
            }
        }
        
        $data = [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'user_id' => $this->message->user_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'metadata' => $this->message->metadata,
                'created_at' => $this->message->created_at->toISOString(),
                'updated_at' => $this->message->updated_at->toISOString(),
                'user' => $this->message->user ? [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                    'email' => $this->message->user->email,
                    'avatar' => $this->message->user->avatar,
                ] : null,
                'attachments' => $attachments
            ]
        ];
        return $data;
    }
}
