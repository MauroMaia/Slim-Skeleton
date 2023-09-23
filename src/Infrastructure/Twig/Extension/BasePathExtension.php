<?php

namespace App\Infrastructure\Twig\Extension;


use App\Infrastructure\Slim\BasePathDetector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BasePathExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('base_path', [$this, 'getBasePath']),
        ];
    }

    public function getBasePath()
    {
       return '/cardeal';
    }
}