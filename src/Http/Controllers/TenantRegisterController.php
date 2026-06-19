<?php

declare(strict_types=1);

namespace SaddlePHP\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use SaddlePHP\Forms\Form;
use SaddlePHP\Saddle;

class TenantRegisterController extends Controller
{
    public function show(): Response
    {
        $handler = app(Saddle::class)->tenantRegistration() ?? abort(404);

        return Inertia::render('Tenancy/Register', [
            'fields' => Form::make()->schema($handler->fields())->toInertia(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $handler = app(Saddle::class)->tenantRegistration() ?? abort(404);

        $form = Form::make()->schema($handler->fields());
        $validated = $request->validate($form->rules());

        $tenant = $handler->register($validated, $request->user());

        $path = trim((string) config('saddle.path', 'admin'), '/');

        return redirect()->to('/'.$path.'/'.$tenant->getRouteKey());
    }
}
