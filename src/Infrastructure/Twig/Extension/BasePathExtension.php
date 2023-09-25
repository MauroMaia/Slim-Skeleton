<?php

namespace App\Infrastructure\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;


class BasePathExtension extends AbstractExtension
{
    public function __construct(private readonly string $basePath) {  }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('base_path', [$this, 'getBasePath']),
        ];
    }

    public function getBasePath(): string
    {
       return $this->basePath;
    }
}