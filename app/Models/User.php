<?php

namespace App\Models;

use App\Models\chatting\Conversation;
use App\Models\chatting\Message;
use App\Models\chatting\MessageAttachment;
use App\Models\chatting\MessageStatus;
use App\Models\social\Follow;
use App\Models\Friendship;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'last_seen_at',
        'is_online',
        'username',
        'bio',
        'profile_completed',
        'gender',
        'banned_at',
        'is_verified',
        'verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected static function boot()
    {
        parent::boot();

        // Override reset password notification
        static::retrieved(function ($user) {
            ResetPassword::createUrlUsing(function ($user, string $token) {
                // Here, you return the API/frontend reset link
                return config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . $user->email;
            });
        });

        // Override email verification notification URL
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());
            
            // Try to get URL from request first (works with current request context)
            // Fallback to APP_URL from config
            try {
                $baseUrl = request()->getSchemeAndHttpHost() . request()->getBasePath();
            } catch (\Exception $e) {
                $baseUrl = config('app.url', 'http://localhost');
            }
            
            // Generate the verification URL
            return $baseUrl . '/email/verify/' . $id . '/' . $hash;
        });
    }

    public function messages():HasMany
    {
        return $this->hasMany(Message::class, 'user_id');
    }
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
            ->withPivot(['joined_at','is_admin', 'last_read_at', 'is_muted', 'notification_enabled'])
            ->withTimestamps();
    }

    public function messageStatuses()
    {
        return $this->hasMany(MessageStatus::class, 'user_id');
    }
    public function messageAttachments()
    {
        return $this->hasMany(MessageAttachment::class, 'user_id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function isFollowing($userId)
    {
        return $this->following()->where('following_id', $userId)->exists();
    }

    public function isFollowedBy($userId)
    {
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    // Friendship relationships
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeVerifiedSubscription()
    {
        return $this->subscriptions()
            ->where('type', 'verified')
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    // Get all friends (accepted friendships)
    public function getFriendsAttribute()
    {
        $friendships = Friendship::where(function ($query) {
            $query->where('user_id', $this->id)
                  ->where('status', 'accepted');
        })->orWhere(function ($query) {
            $query->where('friend_id', $this->id)
                  ->where('status', 'accepted');
        })->get();

        $friendIds = $friendships->map(function ($friendship) {
            return $friendship->user_id === $this->id 
                ? $friendship->friend_id 
                : $friendship->user_id;
        })->toArray();

        return User::whereIn('id', $friendIds)->get();
    }

    public function isFriendWith($userId): bool
    {
        return Friendship::where(function ($query) use ($userId) {
            $query->where('user_id', $this->id)
                  ->where('friend_id', $userId)
                  ->where('status', 'accepted');
        })->orWhere(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('friend_id', $this->id)
                  ->where('status', 'accepted');
        })->exists();
    }

    public function hasPendingRequestTo($userId): bool
    {
        return Friendship::where('user_id', $this->id)
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }

    public function hasPendingRequestFrom($userId): bool
    {
        return Friendship::where('user_id', $userId)
            ->where('friend_id', $this->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function getFriendshipStatus($userId): ?string
    {
        $friendship = Friendship::where(function ($query) use ($userId) {
            $query->where('user_id', $this->id)
                  ->where('friend_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('friend_id', $this->id);
        })->first();

        if (!$friendship) {
            return null;
        }

        if ($friendship->status === 'accepted') {
            return 'friends';
        }

        if ($friendship->user_id === $this->id) {
            return 'request_sent';
        }

        return 'request_received';
    }

    public function isInConversation($conversationId): bool
    {
        return $this->conversations()->where('conversation_id', $conversationId)->exists();
    }

    public function markAsOnline()
    {
        $this->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);
    }
    public function markAsOffline()
    {
        $this->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);
    }
    public function unreadMessagesCount($conversationId = null)
    {
        $query = Message::whereHas('conversation.users', function ($q) {
            $q->where('user_id', $this->id);
        })->where('user_id', '!=', $this->id);

        if ($conversationId) {
            $query->where('conversation_id', $conversationId);
        }

        return $query->whereDoesntHave('statuses', function ($q) {
            $q->where('user_id', $this->id)->where('is_read', true);
        })->count();
    }
    public function isVerified():bool
    {
        return $this->hasVerifiedEmail();
    }

    public function hasVerifiedBadge(): bool
    {
        return $this->is_verified === true && $this->activeVerifiedSubscription() !== null;
    }



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_seen_at' => 'datetime',
        'is_online' => 'boolean',
        'banned_at' => 'datetime',
        'verified_at' => 'datetime',
        ];

}
