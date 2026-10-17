<?php

namespace App\Console\Commands;

use App\Models\User;
use App\UserRole;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('cofesti:create-admin
    {email : Email администратора}
    {--name= : Имя администратора}')]
#[Description('Создаёт первого администратора в интерактивном безопасном режиме.')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Команда требует интерактивный ввод пароля и не принимает пароль через аргументы.');

            return self::FAILURE;
        }

        $email = (string) $this->argument('email');
        $name = (string) ($this->option('name') ?? $this->ask('Имя администратора'));
        $password = (string) $this->secret('Пароль (не менее 12 символов)');
        $passwordConfirmation = (string) $this->secret('Повторите пароль');

        $validator = Validator::make(
            compact('email', 'name', 'password', 'passwordConfirmation'),
            [
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:12', 'same:passwordConfirmation'],
            ],
            [
                'password.same' => 'Пароли не совпадают.',
                'password.min' => 'Пароль должен состоять минимум из 12 символов.',
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->fill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $user->role = UserRole::Administrator;
        $user->save();

        $this->info('Администратор создан.');

        return self::SUCCESS;
    }
}
