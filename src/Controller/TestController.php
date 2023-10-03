<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    path: '/test',name: 'testing',methods: 'GET'
)]
class TestController
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['success1'=>true]);
    }
}