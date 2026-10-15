<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // La CI n'installe jamais Node et ne construit jamais les assets, et
        // public/build est ignore par git : @vite() levait donc
        // ViteManifestNotFoundException et les pages rendues repondaient 500.
        // En local l'echec etait invisible parce que public/hot (marqueur du
        // serveur de dev, lui aussi ignore) fait lire les URL du dev server au
        // lieu du manifeste. withoutVite() neutralise la directive en test.
        $this->withoutVite();
    }
}
