<?php

namespace App\Orchid\Screens\Examples;

use Illuminate\Http\Request;
use App\Models\UserInformation;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
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
                ->modal('createUserModal') // Open the modal to create a new user
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
                TD::make('id', 'ID')->sort()->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->id),
                TD::make('name', 'Name')->sort()->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->name),
                TD::make('email', 'Email')->sort()->filter(Input::make())
                    ->render(fn(UserInformation $user) => $user->email),
                TD::make('phone', 'Phone')
                    ->render(fn(UserInformation $user) => $user->phone),
                TD::make('address', 'Address')
                    ->render(fn(UserInformation $user) => $user->address),
                TD::make('created_at', 'Created At')
                    ->render(fn(UserInformation $user) => $user->created_at ? $user->created_at->toDateTimeString() : 'N/A'),
                TD::make('updated_at', 'Updated At')
                    ->render(fn(UserInformation $user) => $user->updated_at ? $user->updated_at->toDateTimeString() : 'N/A'),
                TD::make('Actions')
                    ->render(function (UserInformation $user) {
                        return Button::make('Edit')
                            ->type(Color::INFO)
                            ->modal('editUserModal')  // Open the edit modal
                            ->method('updateUser', ['id' => $user->id])
                            ->parameters(['user' => $user]) // Passing user data to the modal
                            . Button::make('Delete')
                            ->type(Color::DANGER)
                            ->confirm('Are you sure you want to delete this user?') // Confirmation prompt
                            ->method('deleteUser', ['id' => $user->id]);
                    }),
            ]),

            // Create User Modal
            Layout::modal('createUserModal', [
                Layout::rows([
                    Input::make('name')
                        ->title('Name')
                        ->required()
                        ->placeholder('Enter name')
                        ->value(old('name')) // Keep the old value after submission
                        ->error('name'), // Show error message for name
                    Input::make('email')
                        ->title('Email')
                        ->required()
                        ->placeholder('Enter email')
                        ->value(old('email')) // Keep the old value after submission
                        ->error('email'), // Show error message for email
                    Input::make('phone')
                        ->title('Phone')
                        ->required()
                        ->placeholder('Enter phone number')
                        ->value(old('phone')) // Keep the old value after submission
                        ->error('phone'), // Show error message for phone
                    Input::make('address')
                        ->title('Address')
                        ->required()
                        ->placeholder('Enter address')
                        ->value(old('address')) // Keep the old value after submission
                        ->error('address'), // Show error message for address
                ]),
            ])->title('Create User'),

            // Edit User Modal
            Layout::modal('editUserModal', [
                Layout::rows([
                    Input::make('user.id')->type('hidden'),
                    Input::make('user.name')
                        ->title('Name')
                        ->required()
                        ->placeholder('Enter name')
                        ->value(old('user.name')) // Keep the old value after submission
                        ->error('user.name'), // Show error message for name
                    Input::make('user.email')
                        ->title('Email')
                        ->required()
                        ->placeholder('Enter email')
                        ->value(old('user.email')) // Keep the old value after submission
                        ->error('user.email'), // Show error message for email
                    Input::make('user.phone')
                        ->title('Phone')
                        ->required()
                        ->placeholder('Enter phone number')
                        ->value(old('user.phone')) // Keep the old value after submission
                        ->error('user.phone'), // Show error message for phone
                    Input::make('user.address')
                        ->title('Address')
                        ->required()
                        ->placeholder('Enter address')
                        ->value(old('user.address')) // Keep the old value after submission
                        ->error('user.address'), // Show error message for address
                ]),
            ])->title('Edit User')->async('asyncGetUser'),
        ];
    }

    /**
     * Handle user creation via AJAX.
     */
    public function createUser(Request $request): array
    {
        // Validate input data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user_information,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ], [
            'name.required' => 'Please provide a name.',
            'email.required' => 'Please provide an email address.',
            'phone.required' => 'Please provide a phone number.',
            'address.required' => 'Please provide an address.',
        ]);

        // If validation passes, create a new user
        $user = UserInformation::create($validatedData);

        return [
            'status' => 'success',
            'message' => 'User created successfully!',
            'user' => $user, // Optionally return the user data
        ];
    }

    /**
     * Handle user update via AJAX.
     */
    public function updateUser(Request $request): array
    {
        // Validate input data
        $validatedData = $request->validate([
            'id' => 'required|exists:user_information,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user_information,email,' . $request->input('id'),
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ], [
            'name.required' => 'Please provide a name.',
            'email.required' => 'Please provide an email address.',
            'phone.required' => 'Please provide a phone number.',
            'address.required' => 'Please provide an address.',
        ]);

        // If validation passes, update the user
        $user = UserInformation::findOrFail($request->input('id'))->update($validatedData);

        return [
            'status' => 'success',
            'message' => 'User updated successfully!',
            'user' => $user, // Optionally return the updated user data
        ];
    }

    /**
     * Fetch user data for async editing.
     */
    public function asyncGetUser(UserInformation $user): array
    {
        return [
            'user' => $user, // Return the user data to populate the form in the modal
        ];
    }

    /**
     * Handle user deletion via AJAX.
     */
    public function deleteUser(Request $request): array
    {
        $user = UserInformation::findOrFail($request->input('id'));
        $user->delete();

        return [
            'status' => 'success',
            'message' => 'User deleted successfully!',
        ];
    }
}
