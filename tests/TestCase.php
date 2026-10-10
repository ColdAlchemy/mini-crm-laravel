<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Autentica um usuário (criado na hora, se não vier um). */
    protected function entrar(?User $user = null): User
    {
        $user ??= User::factory()->create();
        $this->actingAs($user);

        return $user;
    }
}
