<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'email'=>$this->email,
            'username'=>$this->username,
            'gender'=>$this->gender,
            'avatar'=>$this->avatar,
            'bio'=>$this->bio,
            'email_verified_at'=>$this->email_verified_at?->toISOString(),
            'is_online'=>$this->is_online,
            'last_seen_at'=>$this->last_seen_at?->toISOString(),
            'created_at'=>$this->created_at->toISOString(),

            'joined_at'=>$this->whenPivotLoaded('conversation_user',fn()=> $this->pivot->joined_at),
            'is_admin'=>$this->whenPivotLoaded('conversation_user',fn()=> $this->pivot->is_admin),
            'last_read_at'=>$this->whenPivotLoaded('conversation_user',fn()=> $this->pivot->last_read_at),
            
            'friendship_status'=>$this->when(isset($this->friendship_status), $this->friendship_status),
            'is_friend'=>$this->when(isset($this->is_friend), $this->is_friend),
            
            'roles'=>$this->whenLoaded('roles', function() {
                return $this->roles->pluck('name');
            }),
            'has_admin_role'=>$this->whenLoaded('roles', function() {
                return $this->roles->contains('name', 'admin');
            }),
            'is_verified'=>$this->is_verified,
            'verified_at'=>$this->verified_at?->toISOString(),


        ];
    }
}
