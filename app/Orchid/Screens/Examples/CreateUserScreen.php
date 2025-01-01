<?php

namespace App\Orchid\Screens\Examples;

use Illuminate\Http\Request;
use App\Models\UserInformation;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Color; // Make sure Color is imported

class CreateUserScreen extends Screen
{
    public function name(): ?string
    {
        return 'Create New User';
    }

    public function description(): ?string
    {
        return 'Fill in the user details to create a new user.';
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('name')->title('Name')->required()->placeholder('Enter name'),
                Input::make('email')->title('Email')->required()->placeholder('Enter email'),
                Input::make('phone')->title('Phone')->required()->placeholder('Enter phone number'),
                Input::make('address')->title('Address')->required()->placeholder('Enter address'),
            ]),
            Button::make('Create User')
                ->method('createUser')
                ->type(Color::SUCCESS), // Use Color::SUCCESS here
        ];
    }

    public function createUser(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user_information,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
        ]);

        UserInformation::create($request->only('name', 'email', 'phone', 'address'));
        Toast::info('User created successfully!');
    }
}
