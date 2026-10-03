<?php

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Livewire\Administrators\Create as CreateAdministrator;
use App\Livewire\Administrators\Edit as EditAdministrator;
use App\Livewire\Administrators\Index as AdministratorIndex;
use App\Livewire\Clubs\Create as CreateClub;
use App\Livewire\Clubs\Edit as EditClub;
use App\Livewire\Statistics\Index;
use App\Livewire\Users\Create as CreateUser;
use App\Livewire\Users\Edit as EditUser;
use App\Livewire\Users\Index as UserIndex;
use App\Models\Club;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('administrator and manager can create clubs and users', function () {
    $administrator = User::factory()->administrator()->create();
    $manager = User::factory()->manager()->create();

    expect($administrator->can(PermissionName::CreateClubs->value))->toBeTrue()
        ->and($administrator->can(PermissionName::CreateUsers->value))->toBeTrue()
        ->and($manager->can(PermissionName::CreateClubs->value))->toBeTrue()
        ->and($manager->can(PermissionName::CreateUsers->value))->toBeTrue();
});

test('a club role can create users but not clubs', function () {
    $club = Club::factory()->create();
    $access = User::factory()->clubAccessFor($club)->create();

    expect($access->hasRole(RoleName::Club))->toBeTrue()
        ->and($access->can(PermissionName::CreateUsers->value))->toBeTrue()
        ->and($access->can(PermissionName::CreateClubs->value))->toBeFalse()
        ->and($access->club_id)->toBe($club->id);

    $this->actingAs($access)->get(route('clubs.index'))->assertForbidden();
});

test('a user role belongs to a club and cannot create clubs or users', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();

    expect($member->hasRole(RoleName::User))->toBeTrue()
        ->and($member->club_id)->toBe($club->id)
        ->and($member->can(PermissionName::CreateClubs->value))->toBeFalse()
        ->and($member->can(PermissionName::CreateUsers->value))->toBeFalse();

    $this->actingAs($member)->get(route('clubs.index'))->assertForbidden();
    $this->actingAs($member)->get(route('users.index'))->assertForbidden();
});

test('a manager can create a club and its club role', function () {
    $manager = User::factory()->manager()->create();

    Livewire::actingAs($manager)
        ->test(CreateClub::class)
        ->set('name', 'North Club')
        ->set('accessName', 'North Access')
        ->set('accessEmail', 'north@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('clubs.index'));

    $club = Club::query()->where('name', 'North Club')->first();

    expect($club)->not->toBeNull()
        ->and($club->access?->hasRole(RoleName::Club))->toBeTrue()
        ->and($club->access?->email)->toBe('north@example.com')
        ->and($club->users()->count())->toBe(1);
});

test('a club access email must be unique on the users table', function () {
    $manager = User::factory()->manager()->create();
    User::factory()->create(['email' => 'mealhada@hosted.pt']);

    Livewire::actingAs($manager)
        ->test(CreateClub::class)
        ->set('name', 'Mealhada')
        ->set('accessName', 'Mealhada Access')
        ->set('accessEmail', 'mealhada@hosted.pt')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasErrors(['accessEmail' => 'unique']);

    expect(Club::query()->where('name', 'Mealhada')->exists())->toBeFalse();
});

test('a club role creates a user in its own club', function () {
    $club = Club::factory()->create();
    $otherClub = Club::factory()->create();
    $access = User::factory()->clubAccessFor($club)->create();
    $team = Team::factory()->create(['club_id' => $club->id]);

    Livewire::actingAs($access)
        ->test(CreateUser::class)
        ->set('name', 'Player')
        ->set('email', 'player@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('clubId', (string) $otherClub->id)
        ->set('teamIds', [(string) $team->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    $player = User::query()->where('email', 'player@example.com')->first();

    expect($player)->not->toBeNull()
        ->and($player->hasRole(RoleName::User))->toBeTrue()
        ->and($player->club_id)->toBe($club->id);
});

test('a club role only sees users from its club', function () {
    $club = Club::factory()->create();
    $otherClub = Club::factory()->create();
    $access = User::factory()->clubAccessFor($club)->create();
    User::factory()->memberOf($club)->create(['name' => 'Own Player']);
    $otherMember = User::factory()->memberOf($otherClub)->create(['name' => 'Other Player']);

    Livewire::actingAs($access)
        ->test(UserIndex::class)
        ->assertSee('Own Player')
        ->assertDontSee('Other Player');

    $this->actingAs($access)
        ->get(route('users.edit', $otherMember))
        ->assertForbidden();
});

test('an administrator creates a user in the selected club', function () {
    $administrator = User::factory()->administrator()->create();
    $club = Club::factory()->create();
    $team = Team::factory()->create(['club_id' => $club->id]);

    Livewire::actingAs($administrator)
        ->test(CreateUser::class)
        ->set('name', 'Player')
        ->set('email', 'player@example.com')
        ->set('clubId', (string) $club->id)
        ->set('teamIds', [(string) $team->id])
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors();

    $player = User::query()->where('email', 'player@example.com')->first();

    expect($player?->hasRole(RoleName::User))->toBeTrue()
        ->and($player?->club_id)->toBe($club->id)
        ->and($player?->teams()->pluck('teams.id')->all())->toBe([$team->id]);
});

test('an administrator can remove all team assignments when editing a user', function () {
    $administrator = User::factory()->administrator()->create();
    $club = Club::factory()->create();
    $team = Team::factory()->create(['club_id' => $club->id]);
    $member = User::factory()->memberOf($club)->withTeams($team)->create();

    Livewire::actingAs($administrator)
        ->test(EditUser::class, ['user' => $member])
        ->set('teamIds', [])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    expect($member->refresh()->teams)->toBeEmpty();
});

test('a user role only collects statistics for assigned teams', function () {
    $club = Club::factory()->create();
    $assignedTeam = Team::factory()->create(['club_id' => $club->id, 'name' => 'Assigned']);
    $otherTeam = Team::factory()->create(['club_id' => $club->id, 'name' => 'Other']);
    $member = User::factory()->memberOf($club)->withTeams($assignedTeam)->create();
    $allowedGame = Game::factory()->create(['team_id' => $assignedTeam->id]);
    $blockedGame = Game::factory()->create(['team_id' => $otherTeam->id]);

    $this->actingAs($member)->get(route('statistics.record', $allowedGame))->assertOk();
    $this->actingAs($member)->get(route('statistics.record', $blockedGame))->assertForbidden();

    Livewire::actingAs($member)
        ->test(Index::class)
        ->assertSee($allowedGame->opponent)
        ->assertDontSee($blockedGame->opponent);
});

test('an administrator creates a manager from the administrators menu', function () {
    $administrator = User::factory()->administrator()->create();

    Livewire::actingAs($administrator)
        ->test(CreateAdministrator::class)
        ->set('name', 'New Manager')
        ->set('email', 'manager@example.com')
        ->set('role', RoleName::Manager->value)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('administrators.index'));

    $manager = User::query()->where('email', 'manager@example.com')->first();

    expect($manager?->hasRole(RoleName::Manager))->toBeTrue()
        ->and($manager?->club_id)->toBeNull();
});

test('an administrator creates another administrator', function () {
    $administrator = User::factory()->administrator()->create();

    Livewire::actingAs($administrator)
        ->test(CreateAdministrator::class)
        ->set('name', 'Second Admin')
        ->set('email', 'second-admin@example.com')
        ->set('role', RoleName::Administrator->value)
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('save')
        ->assertHasNoErrors();

    $created = User::query()->where('email', 'second-admin@example.com')->first();

    expect($created?->hasRole(RoleName::Administrator))->toBeTrue()
        ->and($created?->club_id)->toBeNull();
});

test('users lists club members and administrators lists staff', function () {
    $administrator = User::factory()->administrator()->create(['name' => 'Ada Admin']);
    $club = Club::factory()->create();
    User::factory()->manager()->create(['name' => 'Mia Manager']);
    User::factory()->memberOf($club)->create(['name' => 'Uma User']);
    User::factory()->clubAccessFor($club)->create(['name' => 'Club Access']);

    Livewire::actingAs($administrator)
        ->test(UserIndex::class)
        ->assertSee('Uma User')
        ->assertDontSee('Ada Admin')
        ->assertDontSee('Mia Manager')
        ->assertDontSee('Club Access');

    Livewire::actingAs($administrator)
        ->test(AdministratorIndex::class)
        ->assertSee('Ada Admin')
        ->assertSee('Mia Manager')
        ->assertDontSee('Uma User')
        ->assertDontSee('Club Access');
});

test('a manager cannot open the administrators menu', function () {
    $manager = User::factory()->manager()->create();
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($manager)->get(route('administrators.index'))->assertForbidden();
    $this->actingAs($manager)->get(route('administrators.edit', $administrator))->assertForbidden();
});

test('an administrator cannot edit a club user as staff', function () {
    $administrator = User::factory()->administrator()->create();
    $member = User::factory()->memberOf(Club::factory()->create())->create();

    $this->actingAs($administrator)
        ->get(route('administrators.edit', $member))
        ->assertForbidden();
});

test('an administrator can change a manager into an administrator', function () {
    $administrator = User::factory()->administrator()->create();
    $manager = User::factory()->manager()->create();

    Livewire::actingAs($administrator)
        ->test(EditAdministrator::class, ['user' => $manager])
        ->set('role', RoleName::Administrator->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('administrators.index'));

    $manager->refresh();

    expect($manager->hasRole(RoleName::Administrator))->toBeTrue()
        ->and($manager->hasRole(RoleName::Manager))->toBeFalse()
        ->and($manager->club_id)->toBeNull();
});

test('an administrator can disable a user so they cannot sign in', function () {
    $administrator = User::factory()->administrator()->create();
    $club = Club::factory()->create();
    $team = Team::factory()->create(['club_id' => $club->id]);
    $member = User::factory()->memberOf($club)->withTeams($team)->create();

    Livewire::actingAs($administrator)
        ->test(EditUser::class, ['user' => $member])
        ->set('enabled', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('users.index'));

    expect($member->refresh()->enabled)->toBeFalse();

    auth()->logout();

    $this->post(route('login.store'), [
        'email' => $member->email,
        'password' => 'password',
    ])->assertSessionHasErrorsIn('email');

    $this->assertGuest();
});

test('an administrator can disable a club access', function () {
    $administrator = User::factory()->administrator()->create();
    $club = Club::factory()->create();
    $access = User::factory()->clubAccessFor($club)->create();

    Livewire::actingAs($administrator)
        ->test(EditClub::class, ['club' => $club])
        ->set('accessEnabled', false)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('clubs.index'));

    expect($access->refresh()->enabled)->toBeFalse();
});

test('an administrator cannot disable their own account', function () {
    $administrator = User::factory()->administrator()->create();

    Livewire::actingAs($administrator)
        ->test(EditAdministrator::class, ['user' => $administrator])
        ->set('enabled', false)
        ->call('save')
        ->assertForbidden();

    expect($administrator->refresh()->enabled)->toBeTrue();
});

test('an administrator can delete a club user', function () {
    $administrator = User::factory()->administrator()->create();
    $member = User::factory()->memberOf(Club::factory()->create())->create();

    Livewire::actingAs($administrator)
        ->test(UserIndex::class)
        ->call('confirmDelete', $member->id)
        ->call('deleteConfirmed');

    expect(User::query()->find($member->id))->toBeNull();
});

test('an administrator cannot delete their own account', function () {
    $administrator = User::factory()->administrator()->create();

    Livewire::actingAs($administrator)
        ->test(AdministratorIndex::class)
        ->call('confirmDelete', $administrator->id)
        ->assertForbidden();

    expect(User::query()->find($administrator->id))->not->toBeNull();
});
