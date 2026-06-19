<?php

declare(strict_types=1);

it('resolves the panel translation namespace', function () {
    expect(trans('saddle::panel.actions.save'))->toBe('Save changes')
        ->and(trans('saddle::panel.flash.created', ['resource' => 'Horse']))->toBe('Horse created.');
});
