<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return User::updateOrCreate(
            [
                'admission_number' => $row['admission_number'],
            ],
            [
                'name' => $row['name'],
                'email' => $row['email'],
                'course' => $row['course'],
                'password' => Hash::make('password123'),
            ]
        );
    }
}