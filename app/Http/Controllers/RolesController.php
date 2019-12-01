<?php

namespace App\Http\Controllers;

use App\{Content, Role, User};
use Auth;
use Illuminate\Http\Request;
use RestCord\DiscordClient;

class RolesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'seeUser']);
    }

    /**
     * Sync the database's roles with those on the Discord server
     *
     * @return \Illuminate\Http\Response
     */
    public function syncRoles()
    {
        $roles = Role::all();
        $discord = new DiscordClient(['token' => env('DISCORD_BOT_TOKEN')]);
        $discordRoles = $discord->guild->getGuildRoles(['guild.id' => (int)env('GUILD_ID')]);

        $addedCount = 0;

        foreach ($discordRoles as $role) {
            if (!$roles->contains('discord_id', $role->id) && !$roles->contains('name', $role->name)) {
                $addedCount++;
                Role::create([
                    'name'        => $role->name,
                    'discord_id'  => (int)$role->id,
                    'permissions' => $role->permissions,
                    'position'    => $role->position,
                    'color'       => $role->color,
                    'slug'        => $role->name,
                    'description' => '',
                ]);
            }
        }
        request()->session()->flash('status', $addedCount . ' Discord roles added.');
        redirect()->back();
    }
}
