<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Friendship;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    use ApiResponse;

    // Get all friends
    public function index(Request $request)
    {
        $user = $request->user();

        // Get accepted friendships where user is either sender or receiver
        $friendships = Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'accepted');
        })->orWhere(function ($query) use ($user) {
            $query->where('friend_id', $user->id)
                  ->where('status', 'accepted');
        })->get();

        $friendIds = $friendships->map(function ($friendship) use ($user) {
            return $friendship->user_id === $user->id 
                ? $friendship->friend_id 
                : $friendship->user_id;
        })->toArray();

        $friends = User::whereIn('id', $friendIds)
            ->with('roles', 'permissions')
            ->get();

        return $this->success('Friends retrieved successfully', UserResource::collection($friends));
    }

    // Get friend requests (sent and received)
    public function requests(Request $request)
    {
        $user = $request->user();

        $sentRequests = Friendship::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('friend')
            ->get()
            ->map(function ($friendship) {
                return [
                    'id' => $friendship->id,
                    'user' => new UserResource($friendship->friend),
                    'status' => 'sent',
                    'created_at' => $friendship->created_at,
                ];
            });

        $receivedRequests = Friendship::where('friend_id', $user->id)
            ->where('status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($friendship) {
                return [
                    'id' => $friendship->id,
                    'user' => new UserResource($friendship->user),
                    'status' => 'received',
                    'created_at' => $friendship->created_at,
                ];
            });

        return $this->success('Friend requests retrieved successfully', [
            'sent' => $sentRequests,
            'received' => $receivedRequests,
        ]);
    }

    // Send friend request
    public function sendRequest(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();
        $friendId = $request->friend_id;

        if ($user->id === $friendId) {
            return $this->error('You cannot send a friend request to yourself', 400);
        }

        // Check if friendship already exists
        $existingFriendship = Friendship::where(function ($query) use ($user, $friendId) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($user, $friendId) {
            $query->where('user_id', $friendId)
                  ->where('friend_id', $user->id);
        })->first();

        if ($existingFriendship) {
            if ($existingFriendship->status === 'accepted') {
                return $this->error('You are already friends', 400);
            }
            if ($existingFriendship->status === 'pending') {
                if ($existingFriendship->user_id === $user->id) {
                    return $this->error('Friend request already sent', 400);
                } else {
                    // If other user sent request, accept it
                    $existingFriendship->update([
                        'status' => 'accepted',
                        'accepted_at' => now(),
                    ]);
                    return $this->success('Friend request accepted', new UserResource(User::find($friendId)));
                }
            }
        }

        $friendship = Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        return $this->success('Friend request sent successfully', new UserResource(User::find($friendId)));
    }

    // Accept friend request
    public function acceptRequest(Request $request, $id)
    {
        $user = $request->user();

        $friendship = Friendship::where('id', $id)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $this->success('Friend request accepted', new UserResource($friendship->user));
    }

    // Reject friend request
    public function rejectRequest(Request $request, $id)
    {
        $user = $request->user();

        $friendship = Friendship::where('id', $id)
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->delete();

        return $this->success('Friend request rejected');
    }

    // Remove friend or cancel request
    public function remove(Request $request, $friendId)
    {
        $user = $request->user();

        $friendship = Friendship::where(function ($query) use ($user, $friendId) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($user, $friendId) {
            $query->where('user_id', $friendId)
                  ->where('friend_id', $user->id);
        })->firstOrFail();

        $friendship->delete();

        return $this->success('Friend removed successfully');
    }

    // Check friendship status with a user
    public function checkStatus(Request $request, $userId)
    {
        $user = $request->user();
        $status = $user->getFriendshipStatus($userId);

        return $this->success('Friendship status retrieved', [
            'status' => $status,
            'is_friend' => $user->isFriendWith($userId),
        ]);
    }
}
