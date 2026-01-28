<?php

namespace App\Filament\Resources\Students\Schemas;


use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;      // ← use this
use Filament\Forms\Components\TextInput;   // ← and this
use Illuminate\Support\Str;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Siswa')
                    ->description('Informasi siswa peserta ekstrakurikuler')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->unique('students', 'nisn', ignoreRecord: true)
                            ->numeric()
                            ->length(10),

                        TextInput::make('nama')
                            ->label('Nama Siswa')
                            ->required(),

                        TextInput::make('kelas')
                            ->label('Kelas')
                            ->required()
                            ->hint('Contoh: X-1, XI-2, XII-3'),

                        TextInput::make('qr_token')
                            ->label('QR Token')
                            ->disabled()
                            ->dehydrated()
                            ->default(fn () => (string) Str::uuid())
                            ->columnSpanFull(),
                    ]),
                Section::make('Akun Login')
                    ->description('Data login siswa di sistem')
                    ->columns(2)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required()
                            ->minLength(6)
                            ->dehydrated(fn ($state) => filled($state)),
                    ]),
            ]);
    }
}
