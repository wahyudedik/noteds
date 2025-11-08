<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ForumPreferenceController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $preferences = $user->forum_email_preferences;

        return view('forum.preferences', compact('preferences'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $defaults = $user->defaultForumEmailPreferences();

        $request->validate(
            collect($defaults)->mapWithKeys(fn ($value, $key) => [$key => 'nullable|boolean'])->toArray()
        );

        $updated = [];
        foreach ($defaults as $key => $value) {
            $updated[$key] = $request->boolean($key);
        }

        $user->forum_email_preferences = $updated;
        $user->save();

        return redirect()->route('forum.preferences.edit')->with('success', 'Forum email preferences updated successfully.');
    }
}
