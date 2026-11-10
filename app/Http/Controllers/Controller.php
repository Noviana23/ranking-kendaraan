<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function page(string $view, array $data = [], string $title = 'Ranking Kendaraan', string $active = '')
    {
        // Render file PHP biasa dari resources/views, bukan file Blade.
        $content = view($view, $data)->render();
        return response(view('layout', [
            'content' => $content,
            'title' => $title,
            'active' => $active,
            'currentUser' => auth()->user() ?? null,
            'sessionUser' => [
                'id' => session('user_id'),
                'name' => session('user_name'),
                'role' => session('user_role'),
            ],
        ])->render());
    }
}
