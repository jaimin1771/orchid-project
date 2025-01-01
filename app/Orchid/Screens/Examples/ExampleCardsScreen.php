<?php

namespace App\Orchid\Screens\Examples;

use Illuminate\Http\Request;
use App\Models\User;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Screen;
use Orchid\Screen\Sight;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExampleCardsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'user' => User::firstOrFail(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'User Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Manage users including create, update, and delete operations.';
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Create User')
                ->type(Color::SUCCESS)
                ->modal('createUserModal')
                ->method('createUser'),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            Layout::legend('user', [
                Sight::make('id'),
                Sight::make('name'),
                Sight::make('email'),
                Sight::make('created_at', 'Created'),
                Sight::make('updated_at', 'Updated'),
                Sight::make('Actions')->render(
                    fn(User $user) =>
                    Button::make('Edit')
                        ->type(Color::INFO)
                        ->modal('editUserModal')
                        ->method('updateUser', ['id' => $user->id]) .
                        Button::make('Delete')
                        ->type(Color::DANGER)
                        ->confirm('Are you sure you want to delete this user?')
                        ->method('deleteUser', ['id' => $user->id])
                ),
            ])->title('User Details'),

            Layout::modal('createUserModal', [
                Layout::rows([
                    Input::make('name')->title('Name')->required(),
                    Input::make('email')->title('Email')->required(),
                ]),
            ])->title('Create User'),

            Layout::modal('editUserModal', [
                Layout::rows([
                    Input::make('user.id')->type('hidden'),
                    Input::make('user.name')->title('Name')->required(),
                    Input::make('user.email')->title('Email')->required(),
                ]),
            ])->title('Edit User')->async('asyncGetUser'),
        ];
    }

    /**
     * Handle user creation.
     */
    public function createUser(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        User::create($request->only('name', 'email'));
        Toast::info('User created successfully!');
    }

    /**
     * Handle user update.
     */
    public function updateUser(Request $request): void
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->input('id'),
        ]);

        User::findOrFail($request->input('id'))->update($request->only('name', 'email'));
        Toast::info('User updated successfully!');
    }

    /**
     * Handle user deletion.
     */
    public function deleteUser(Request $request): void
    {
        $request->validate([
            'id' => 'required|exists:users,id',
        ]);

        User::findOrFail($request->input('id'))->delete();
        Toast::info('User deleted successfully!');
    }

    /**
     * Fetch user data for async editing.
     */
    public function asyncGetUser(User $user): array
    {
        return [
            'user' => $user,
        ];
    }
}
