<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(app_path('Saddle'));
});

it('scaffolds a relation manager class', function () {
    $this->artisan('saddle:relation', ['name' => 'PostsRelationManager', '--relationship' => 'posts'])
        ->assertSuccessful();

    $path = app_path('Saddle/RelationManagers/PostsRelationManager.php');
    expect(File::exists($path))->toBeTrue();

    $contents = File::get($path);
    expect($contents)
        ->toContain('class PostsRelationManager extends RelationManager')
        ->toContain("protected static string \$relationship = 'posts';");
});

it('guesses the relationship from the class name when omitted', function () {
    $this->artisan('saddle:relation', ['name' => 'PostsRelationManager'])->assertSuccessful();

    expect(File::get(app_path('Saddle/RelationManagers/PostsRelationManager.php')))
        ->toContain("protected static string \$relationship = 'posts';");
});
