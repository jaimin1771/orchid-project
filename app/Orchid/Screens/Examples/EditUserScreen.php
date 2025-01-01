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

class EditUserScreen extends Screen
{
    public $user;

    public function query(UserInformation $user): iterable
    {
        $this->user = $user;
        return [
            'user' => $user
        ];
    }

    public function name(): ?string
    {
        return 'Edit User';
    }

    public function description(): ?string
    {
        return 'Modify the user details below.';
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('user.name')->title('Name')->required()->placeholder('Enter name')->value($this->user->name),
                Input::make('user.email')->title('Email')->required()->placeholder('Enter email')->value($this->user->email),
                Input::make('user.phone')->title('Phone')->required()->placeholder('Enter phone number')->value($this->user->phone),
                Input::make('user.address')->title('Address')->required()->placeholder('Enter address')->value($this->user->address),
            ]),
            Button::make('Save Changes')
                ->method('updateUser')
                ->type(Color::SUCCESS), // Use Color::SUCCESS here
        ];
    }

    public function updateUser(Request $request): void
    {
        $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|unique:user_information,email,' . $this->user->id,
            'user.phone' => 'required|string|max:20',
            'user.address' => 'required|string|max:255',
        ]);

        $this->user->update($request->input('user'));
        Toast::info('User updated successfully!');
    }
}
