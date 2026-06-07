<?php

namespace Security\Fixtures;

class VulnerableExample
{
    public function deserialize(string $payload): mixed
    {
        return unserialize($payload);
    }

    public function run(string $command): string
    {
        return shell_exec($command) ?? '';
    }
}
