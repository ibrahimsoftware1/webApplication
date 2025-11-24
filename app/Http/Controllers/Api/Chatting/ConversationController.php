<?php

namespace App\Http\Controllers\Api\Chatting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversation\AddParticipantsRequest;
use App\Http\Requests\Conversation\StoreConversationRequest;
use App\Http\Requests\Conversation\UpdateConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\chatting\Conversation;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    use ApiResponse;

    //Users Conversations

    public function index(Request $request){
        $user = $request->user();
        
        // Ensure user is in the community chat (with error handling)
        try {
            $this->ensureUserInCommunityChat($user);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to ensure user in community chat: ' . $e->getMessage());
        }
        
        // Get conversations excluding Community Chat
        $conversations=$user
            ->conversations()
            ->where(function($query) {
                $query->where('name', '!=', 'Community Chat')
                      ->orWhereNull('name');
            })
            ->with('users','lastMessage.user')
            ->withCount('messages')
            ->orderBy('last_message_at','desc')
            ->paginate(10);

        return $this->success('Conversations retrieved successfully',
            ConversationResource::collection($conversations)->response()->getData(true));
    }

    // Get community chat (public endpoint for frontend)
    public function getCommunityChat(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is in the community chat
        $this->ensureUserInCommunityChat($user);
        
        // Get the community chat
        $communityChat = Conversation::where('name', 'Community Chat')
            ->where('type', 'group')
            ->with('users', 'lastMessage.user')
            ->first();

        if (!$communityChat) {
            return $this->error('Community chat not found', null, 404);
        }

        return $this->success('Community chat retrieved successfully',
            new ConversationResource($communityChat));
    }

    // Get or create community chat and ensure user is in it
    private function ensureUserInCommunityChat(User $user)
    {
        // Find or create the community chat
        $communityChat = Conversation::where('name', 'Community Chat')
            ->where('type', 'group')
            ->first();

        if (!$communityChat) {
            // Create community chat if it doesn't exist
            $communityChat = Conversation::create([
                'type' => 'group',
                'name' => 'Community Chat',
                'description' => 'General community chat for everyone',
                'created_by' => $user->id, // First user to access becomes creator
            ]);
        }

        // Ensure user is in the community chat
        if (!$user->isInConversation($communityChat->id)) {
            $communityChat->addParticipants([$user->id], false);
        }
    }


    //create new conversation
    public function store(StoreConversationRequest $request){
        DB::beginTransaction();
        try {
            $currentUser = auth()->user();
            
            // For private conversations, check if users are friends
            if($request->type==='private' && count($request->user_ids)===1){
                $otherUserId = $request->user_ids[0];
                
                // Check if users are friends
                if(!$currentUser->isFriendWith($otherUserId)){
                    DB::rollBack();
                    return $this->error('You must be friends to start a private conversation', [
                        'user_id' => $otherUserId,
                        'requires_friendship' => true
                    ], 403);
                }
                
                $existConversation=$this->findExistingPrivateConversation(
                    auth()->id(),
                    $otherUserId
                );

                if($existConversation){
                    DB::commit();
                    return $this->success('Conversation Already Exists',
                    new ConversationResource($existConversation->load('users')));
                }
            }

            // For group conversations, prevent creating duplicate "Community Chat"
            if($request->type==='group' && $request->name === 'Community Chat'){
                // Check if Community Chat already exists
                $existingCommunityChat = Conversation::where('name', 'Community Chat')
                    ->where('type', 'group')
                    ->first();
                
                if($existingCommunityChat){
                    // If user is not in it, add them
                    if(!$currentUser->isInConversation($existingCommunityChat->id)){
                        $existingCommunityChat->addParticipants([$currentUser->id], false);
                    }
                    DB::commit();
                    return $this->success('Community Chat already exists',
                        new ConversationResource($existingCommunityChat->load('users')));
                }
            }

            //create conversation
            $conversation=Conversation::create([
                'type'=>$request->type,
                'name'=>$request->name,
                'description'=>$request->description,
                'created_by'=>auth()->id()
            ]);

            //add creator as admin
            $participants=[
                auth()->id()=>[
                    'joined_at'=>now(),
                    'is_admin'=>true,
                    'notification_enabled'=>true,]
            ];
            //add other users
            foreach($request->user_ids as $userId){
                if($userId!=auth()->id()){
                    $participants[$userId]=[
                        'joined_at'=>now(),
                        'is_admin'=>false,
                        'notification_enabled'=>true,
                    ];
                }
            }
            $conversation->users()->attach($participants);
            DB::commit();
            return $this->success('Conversation created successfully',
            new ConversationResource($conversation->load('users')),
                201);
        }
        catch (\Exception $e){
            DB::rollBack();
            return $this->error('Failed to create conversation',null,500);
        }
    }

    //Conversation Details
    public function show(Conversation $conversation)
    {
            if (!auth()->user()->isInConversation($conversation->id)) {
                return $this->error('You are not a participant of this conversation', null, 403);
            }
            $conversation->load(['users', 'messages' => function ($query) {
                $query->latest()->limit(50);
            }]);
            $conversation->markAsRead(auth()->id());
            return $this->success('Conversation details retrieved successfully',
                new ConversationResource($conversation)
            );

    }

    //update Conversation For Group type

    public function update(UpdateConversationRequest $request,Conversation $conversation){

        if(!$conversation->isGroup()){
            return $this->error('Cannot Update Private Conversation',null,403);
        }

        //if user is admin
        $isAdmin=$conversation->users()
            ->wherePivot('user_id',auth()->id())
            ->wherePivot('is_admin',true)
            ->exists();

        if(!$isAdmin){
            return $this->error('Only Admin Can Update This Conversation',null,403);
        }

        $conversation->update($request->validated());

        return $this->success('Conversation updated successfully',
        new ConversationResource($conversation->load('users')));
    }

    public function destroy(Conversation $conversation){

        //checks if user is in this conversation
        if(!auth()->user()->isInConversation($conversation->id)){
            return $this->error('You Are Not Participant of this Conversation',null,403);
        }
        if($conversation->isPrivate() || $conversation->users()->count()===1){
            $conversation->messages()->delete();
            $conversation->users()->detach();
            $conversation->delete();

            return $this->success('Conversation deleted successfully');
        }

        $conversation->removeParticipants([auth()->id()]);
        return $this->success('You left Conversation successfully');
    }

    //Add members to the group

    public function addParticipants(AddParticipantsRequest $request,Conversation $conversation){

        if(!$conversation->isGroup()){
            return $this->error('You cannot Add Participants to Private Conversation');
        }

        $isAdmin=$conversation->users()
            ->wherePivot('user_id',auth()->id())
            ->wherePivot('is_admin',true)
            ->exists();

        if(!$isAdmin){
            return $this->error('Only Admin Can Add Participants to Private Conversation');
        }

        $conversation->addParticipants($request->user_ids);
        return $this->success('Participant added successfully',
        new ConversationResource($conversation->load('users'))
        );

    }

    //Remove members from group
    public function removeParticipant(Conversation $conversation,User $user){

        if(!$conversation->isGroup()){
            return $this->error('You cannot Remove Participants from private Conversation');
        }

        $isAdmin=$conversation->users()
            ->wherePivot('user_id',auth()->id())
            ->wherePivot('is_admin',true)
            ->exists();
        if(!$isAdmin && $user->id !== auth()->id()){
            return $this->error('Only Admins can remove Participants');
        }
        $conversation->removeParticipants([$user->id]);
        return $this->success('Participant removed successfully');
    }

    public function markAsRead(Conversation $conversation){

        if(!auth()->user()->isInConversation($conversation->id)){
            return $this->error('You are not a participant of this conversation', 403);
        }

        $conversation->markAsRead(auth()->id());
        return $this->success('Conversation marked as read successfully', 200);
    }

    //Get conversation messages
    public function messages(Conversation $conversation){
        $user = auth()->user();

        // Auto-join community chat if user is not in it
        if (!$user->isInConversation($conversation->id)) {
            // Allow access to community chat (group conversation named "Community Chat")
            if ($conversation->isGroup() && $conversation->name === 'Community Chat') {
                $conversation->addParticipants([$user->id], false);
            } else {
                return $this->error('You are not a participant of this conversation', 403);
            }
        }

        $messages=$conversation->messages()
            ->with(['user','attachments'])
            ->latest()
            ->paginate(50);

        return $this->success('Messages retrieved successfully',
        MessageResource::collection($messages)->response()->getData(true));
    }

    // Join a conversation (especially for community chat)
    public function join(Conversation $conversation)
    {
        $user = auth()->user();
        
        // Check if user is already in the conversation
        if ($user->isInConversation($conversation->id)) {
            return $this->success('You are already in this conversation', 
                new ConversationResource($conversation->load('users')));
        }
        
        // For community chat (group conversation named "Community Chat"), allow anyone to join
        if ($conversation->isGroup() && $conversation->name === 'Community Chat') {
            $conversation->addParticipants([$user->id], false);
            return $this->success('Joined community chat successfully', 
                new ConversationResource($conversation->load('users')));
        }
        
        // For other group conversations, check if user is admin or if it's open
        if ($conversation->isGroup()) {
            // Allow joining if it's a group (you can add more restrictions here)
            $conversation->addParticipants([$user->id], false);
            return $this->success('Joined conversation successfully', 
                new ConversationResource($conversation->load('users')));
        }
        
        // Private conversations cannot be joined
        return $this->error('You cannot join a private conversation', null, 403);
    }

    //Find Private Conversation if exists
    private function findExistingPrivateConversation($userId1, $userId2)
    {
        return Conversation::where('type', 'private')
            ->whereHas('users', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('users', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();
    }




}
