<?php

namespace App\Http\Controllers;

use App\Events\JoinGroupChat;
use App\Events\NewGroupMessage;
use App\Events\NewGroupNotification;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class GroupController extends Controller
{
    public function listGroups()
    {
        $groups = auth()->user()->groups;

        return view('layouts.app', compact('groups'));
    }
    public function show(Group $group)
    {
        // Make sure the authenticated user is a member of the group
        if (!$group->users->contains(auth()->id())) {
            abort(403, 'You are not a member of this group.');
        }

        // Load necessary relationships for the group
        $group->load(['creator', 'admin', 'users', 'messages' => function ($query) {
            $query->with('user');
        }]);

        // Get the messages for the group chat
        $messages = $group->messages ?? collect();

        // Get the current user's ID
        $userId = auth()->id();

        // Generate the invitation link
        $invitationLink = route('group.join', ['group' => $group, 'token' => $group->invitation_token]);

        return view('groups.show', compact('group', 'messages', 'userId', 'invitationLink'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return view('groups.create', compact('users'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'user_ids' => 'array', // Make sure it's an array of user IDs
            'user_ids.*' => 'exists:users,id', // Validate each user ID exists in the users table
        ]);

        if ($validator->fails()) {
            return redirect()->route('group.create')
                ->withErrors($validator)
                ->withInput();
        }

        $group = new Group();
        $group->name = $request->name;
        $group->invitation_token = Str::random(16);
        $group->creator_id = auth()->id();
        $group->admin_id = auth()->id(); // Initially set the admin as the creator
        $group->save();

        // Attach the creator to the group users
        $group->users()->syncWithoutDetaching([auth()->id()]);

        // Attach additional users if provided
        if ($request->has('user_ids')) {
            $group->users()->syncWithoutDetaching($request->user_ids);
        }
        foreach ($request->user_ids as $userId) {
            event(new NewGroupNotification($userId, $group));
        }

        $invitationLink = route('group.join', ['group' => $group, 'token' => $group->invitation_token]);

        return redirect()->route('group.show', $group)->with('invitationLink', $invitationLink);
    }
    public function joinGroupByToken(Group $group, $token)
    {

        if ($group->invitation_token === $token) {
            $group->users()->syncWithoutDetaching([auth()->id()]);
            event(new JoinGroupChat($group));
            return redirect()->route('group.show', $group);
        } else {
            // Invalid invitation token
            abort(404);
        }
    }
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $group = Group::findOrFail($request->group_id);
        $message = GroupMessage::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'group_id' => $group->id
        ]);
        event(new NewGroupMessage($message));
    }

    public function updateAdmin(Group $group, User $user)
    {
        // Ensure the authenticated user is the creator of the group
        if ($group->creator_id === auth()->id()) {
            $group->admin_id = $user->id;
            $group->save();
        }

        return back();
    }
    public function addUserToGroupForm(Group $group)
    {
        $availableUsers = User::whereNotIn('id', $group->users->pluck('id'))->get();

        return view('groups.add-users', compact('group', 'availableUsers'));
    }

    public function addUserToGroup(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);
        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Ensure the authenticated user is the creator of the group
        if ($group->creator_id !== auth()->id()) {
            return back()->with('error', 'Only the group creator can add users.');
        }

        $group->users()->syncWithoutDetaching($request->user_ids);; // Get the group name
        foreach ($request->user_ids as $userId) {
            event(new NewGroupNotification($userId, $group));
        }

        return redirect()->route('group.show', ['group' => $group])
            ->with('success', 'Users added to the group successfully.');
    }

    public function removeUserFromGroup(Group $group, User $user)
    {
        if ($group->creator_id === auth()->id() && $user->id !== auth()->id()) {
            $group->users()->detach($user);
        }

        return back();
    }
}
