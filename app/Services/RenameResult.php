<?php

namespace App\Services;

class RenameResult
{
    public function __construct(
        public string $oldSlug,
        public string $newSlug,
        public string $oldDomain,
        public string $newDomain,
        public string $oldDatabase,
        public string $newDatabase,
    ) {
    }
}

