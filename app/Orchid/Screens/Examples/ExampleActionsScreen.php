<?php

namespace App\Orchid\Screens\Examples;

use Illuminate\Http\Request;
use App\Models\UserInformation; // Assuming you have created a model for UserInformation table
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;


class ExampleActionsScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'users' => UserInformation::paginate(10), // Paginate users from UserInformation table
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
            Layout::table('users', [
                TD::make('id', 'ID')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->id),
                TD::make('name', 'Name')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->name),
                TD::make('email', 'Email')
                    ->sort()
                    ->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->email),
                TD::make('phone', 'Phone')
                    ->render(fn(UserInformation $user) => $user->phone),
                TD::make('address', 'Address')
                    ->render(fn(UserInformation $user) => $user->address),
                TD::make('created_at', 'Created At')
                    ->render(fn(UserInformation $user) => $user->created_at->toDateTimeString()),
                TD::make('updated_at', 'Updated At')
                    ->render(fn(UserInformation $user) => $user->updated_at->toDateTimeString()),
                TD::make('Actions')
                    ->render(function (UserInformation $user) {
                        return Button::make('Edit')
                            ->type(Color::INFO)
                            ->modal('editUserModal')
                            ->method('updateUser', ['id' => $user->id])
                            . Button::make('Delete')
                            ->type(Color::DANGER)
                            ->confirm('Are you sure you want to delete this user?')
                            ->method('deleteUser', ['id' => $user->id]);
                    }),
            ]),

            Layout::modal('createUserModal', [
                Layout::rows([
                    Input::make('name')
                        ->title('Name')
                        ->required()
                        ->placeholder('Enter name'),
                    Input::make('email')
                        ->title('Email')
                        ->required()
                        ->placeholder('Enter email'),
                    Input::make('phone')
                        ->title('Phone')
                        ->required()
                        ->placeholder('Enter phone number'),
                    Input::make('address')
                        ->title('Address')
                        ->required()
                        ->placeholder('Enter address'),
                ]),
            ])->title('Create User'),

            Layout::modal('editUserModal', [
                Layout::rows([
                    Input::make('user.id')
                        ->type('hidden'),
                    Input::make('user.name')
                        ->title('Name')
                        ->required()
                        ->placeholder('Enter name'),
                    Input::make('user.email')
                        ->title('Email')
                        ->required()
                        ->placeholder('Enter email'),
                    Input::make('user.phone')
                        ->title('Phone')
                        ->required()
                        ->placeholder('Enter phone number'),
                    Input::make('user.address')
                        ->title('Address')
                        ->required()
                        ->placeholder('Enter address'),
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
            'email' => 'required|email|unique:UserInformation,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'phone.required' => 'The phone field is required.',
            'address.required' => 'The address field is required.',
        ]);

        UserInformation::create($request->only('name', 'email', 'phone', 'address'));
        Toast::info('User created successfully!');
    }

    /**
     * Handle user update.
     */
    public function updateUser(Request $request): void
    {
        $request->validate([
            'id' => 'required|exists:UserInformation,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:UserInformation,email,' . $request->input('id'),
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'phone.required' => 'The phone field is required.',
            'address.required' => 'The address field is required.',
        ]);

        UserInformation::findOrFail($request->input('id'))->update($request->only('name', 'email', 'phone', 'address'));
        Toast::info('User updated successfully!');
    }

    /**
     * Handle user deletion.
     */
    public function deleteUser(Request $request): void
    {
        $request->validate([
            'id' => 'required|exists:UserInformation,id',
        ], [
            'id.required' => 'User ID is required for deletion.',
        ]);

        UserInformation::findOrFail($request->input('id'))->delete();
        Toast::info('User deleted successfully!');
    }

    /**
     * Fetch user data for async editing.
     */
    public function asyncGetUser(UserInformation $user): array
    {
        return [
            'user' => $user,
        ];
    }
}
