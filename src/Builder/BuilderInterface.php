<?php


namespace App\Builder;


interface BuilderInterface
{
    public function build(array $options): array;

    public function buildEmpty(): array;
}
