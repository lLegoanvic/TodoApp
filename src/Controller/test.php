<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class test extends  AbstractController
{
    #[Route('/api/{id}', name: 'apitoto')]
    public function toto(): jsonResponse
    {
        return new jsonResponse('toto');
    }
}